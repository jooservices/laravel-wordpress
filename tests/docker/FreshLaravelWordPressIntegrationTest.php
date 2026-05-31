<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jooservices\LaravelWordPress\DTOs\Credentials\CredentialCreateData;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\AuthType;
use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Models\Post;
use Tests\TestCase;

final class FreshLaravelWordPressIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_laravel_app_syncs_with_real_wordpress_runtime(): void
    {
        $report = $this->baseReport();
        $failures = [];

        try {
            $this->assertAndRecord($report, $failures, 'wp_cli_installed', trim((string) shell_exec('command -v wp')) !== '');
            $this->assertAndRecord($report, $failures, 'wordpress_http_available', Http::get($this->wordpressUrl().'/wp-json')->successful());
            $this->assertAndRecord($report, $failures, 'package_config_loaded', config('wordpress.table_prefix') === 'wp_');
            $this->assertAndRecord($report, $failures, 'package_tables_migrated', DB::getSchemaBuilder()->hasTable('wp_posts') && DB::getSchemaBuilder()->hasTable('wp_media_items'));

            $site = WordPress::sites()->create(new SiteCreateData('Docker WordPress', $this->wordpressUrl()));
            WordPress::credentials()->createForSite($site, new CredentialCreateData(
                name: 'Docker',
                authType: AuthType::ApplicationPassword,
                username: getenv('WORDPRESS_ADMIN_USER') ?: 'admin',
                secret: trim((string) file_get_contents('/tmp/laravel-wordpress-app-password')),
            ));

            $seed = $this->wordpressSeed();
            $beforePostCount = Post::query()->count();
            $pullResult = WordPress::site($site)->content()->posts()->pull(['per_page' => 20]);
            WordPress::site($site)->media()->pull(['per_page' => 20]);

            $post = Post::query()->where('remote_id', $seed['WORDPRESS_SEED_POST_ID'])->first();
            $featured = MediaItem::query()->where('remote_id', $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'])->first();
            $inline = MediaItem::query()->where('remote_id', $seed['WORDPRESS_SEED_INLINE_MEDIA_ID'])->first();

            $this->assertAndRecord($report, $failures, 'wordpress_post_pulled_to_laravel', $post !== null, ['remote_id' => $seed['WORDPRESS_SEED_POST_ID']]);
            $this->assertAndRecord($report, $failures, 'wordpress_media_pulled_to_laravel', $featured !== null && $inline !== null);

            if ($post !== null) {
                $raw = $post->raw_payload;
                $this->assertAndRecord($report, $failures, 'post_title_matches', data_get($raw, 'title.rendered') === 'Docker Integration Original Post');
                $this->assertAndRecord($report, $failures, 'post_author_matches', (int) data_get($raw, 'author') === $seed['WORDPRESS_SEED_AUTHOR_ID']);
                $categoryIds = array_map('intval', data_get($raw, 'categories', []));
                $tagIds = array_map('intval', data_get($raw, 'tags', []));
                $this->assertAndRecord($report, $failures, 'post_category_matches', in_array((int) $seed['WORDPRESS_SEED_CATEGORY_ID'], $categoryIds, true), [
                    'expected' => (int) $seed['WORDPRESS_SEED_CATEGORY_ID'],
                    'actual' => $categoryIds,
                ]);
                $this->assertAndRecord($report, $failures, 'post_tag_matches', in_array((int) $seed['WORDPRESS_SEED_TAG_ID'], $tagIds, true), [
                    'expected' => (int) $seed['WORDPRESS_SEED_TAG_ID'],
                    'actual' => $tagIds,
                ]);
                $this->assertAndRecord($report, $failures, 'featured_media_reference_matches', (int) data_get($raw, 'featured_media') === $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID']);
                $this->assertAndRecord($report, $failures, 'inline_media_reference_matches', str_contains((string) data_get($raw, 'content.rendered'), (string) optional($inline)->source_url));
            }

            if ($featured !== null) {
                WordPress::site($site)->media()->downloadFile($featured);
                $featured->refresh();
                $this->assertAndRecord($report, $failures, 'featured_media_file_downloaded', $featured->local_path !== null && Storage::disk((string) $featured->local_disk)->exists((string) $featured->local_path), [
                    'path' => $featured->local_path,
                    'size' => $featured->file_size,
                ]);
            }

            WordPress::site($site)->content()->posts()->pull(['per_page' => 20]);
            $this->assertAndRecord($report, $failures, 'pull_is_idempotent_for_posts', Post::query()->count() === $beforePostCount + $pullResult->succeeded);

            shell_exec('wp post update '.$seed['WORDPRESS_SEED_POST_ID'].' --post_title='.escapeshellarg('Docker Integration Updated Post').' --path='.escapeshellarg($this->wordpressPath()).' --allow-root');
            WordPress::site($site)->content()->posts()->pullOne($seed['WORDPRESS_SEED_POST_ID']);
            $updated = Post::query()->where('remote_id', $seed['WORDPRESS_SEED_POST_ID'])->first();
            $this->assertAndRecord($report, $failures, 'wordpress_update_updates_laravel_record', Post::query()->where('remote_id', $seed['WORDPRESS_SEED_POST_ID'])->count() === 1 && data_get($updated?->raw_payload, 'title.rendered') === 'Docker Integration Updated Post');

            $report['sync'] = [
                'pull' => [
                    'supported' => true,
                    'tested' => true,
                    'passed' => ! $this->hasFailed($report, ['wordpress_post_pulled_to_laravel', 'pull_is_idempotent_for_posts', 'wordpress_update_updates_laravel_record']),
                    'created' => $pullResult->succeeded,
                    'updated' => 1,
                    'deleted_or_unpublished' => 0,
                    'mismatches' => $this->failedAssertions($report),
                ],
                'push' => [
                    'supported' => true,
                    'tested' => false,
                    'passed' => false,
                    'created' => 0,
                    'updated' => 0,
                    'deleted_or_unpublished' => 0,
                    'mismatches' => [],
                    'reason' => 'Generic push services exist, but this smoke test does not create Laravel-originated posts because the current Post model stores WordPress REST response payloads without a post-specific DTO for author, taxonomy, meta, featured media, and rendered content mapping.',
                ],
                'idempotency' => [
                    'tested' => true,
                    'passed' => ! $this->hasFailed($report, ['pull_is_idempotent_for_posts']),
                    'duplicates' => Post::query()->select('remote_id')->groupBy('remote_id')->havingRaw('count(*) > 1')->pluck('remote_id')->all(),
                ],
            ];
            $report['wordpress'] = $this->wordpressSummary($seed);
            $report['laravel'] = $this->laravelSummary();
            $report['capabilities'] = [
                'pull_supported' => true,
                'push_supported' => true,
                'media_sync_supported' => true,
                'wp_cli_supported' => true,
                'wp_bootstrap_supported' => File::exists($this->wordpressPath().'/wp-load.php'),
            ];
            $report['limitations'][] = $report['sync']['push']['reason'];
        } finally {
            $report['status'] = $failures === [] ? 'passed' : 'failed';
            $report['failures'] = $failures;
            $this->writeReport($report);
        }

        self::assertSame([], $failures, json_encode($failures, JSON_PRETTY_PRINT));
    }

    private function assertAndRecord(array &$report, array &$failures, string $name, bool $passed, array $details = []): void
    {
        $report['assertions'][] = ['name' => $name, 'status' => $passed ? 'passed' : 'failed', 'details' => $details];
        if (! $passed) {
            $failures[] = ['name' => $name, 'details' => $details];
        }
    }

    private function wordpressSeed(): array
    {
        $values = [];
        foreach (file('/tmp/wordpress-seed.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            [$key, $value] = explode('=', $line, 2);
            $values[$key] = (int) $value;
        }

        return $values;
    }

    private function baseReport(): array
    {
        return [
            'status' => 'failed',
            'generated_at' => now()->toIso8601String(),
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'wordpress_version' => trim((string) shell_exec('wp core version --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
                'mysql_or_mariadb_version' => trim((string) DB::selectOne('select version() as version')->version),
                'package_name' => 'jooservices/laravel-wordpress',
                'package_version_or_ref' => trim((string) shell_exec('cd '.escapeshellarg((string) getenv('PACKAGE_PATH')).' && git rev-parse --short HEAD 2>/dev/null')),
            ],
            'capabilities' => [
                'pull_supported' => false,
                'push_supported' => false,
                'media_sync_supported' => false,
                'wp_cli_supported' => false,
                'wp_bootstrap_supported' => false,
            ],
            'wordpress' => [],
            'laravel' => [],
            'sync' => [],
            'assertions' => [],
            'failures' => [],
            'limitations' => [],
        ];
    }

    private function wordpressSummary(array $seed): array
    {
        return [
            'database' => [
                'posts' => (int) shell_exec('wp post list --post_type=post --format=count --path='.escapeshellarg($this->wordpressPath()).' --allow-root'),
                'pages' => (int) shell_exec('wp post list --post_type=page --format=count --path='.escapeshellarg($this->wordpressPath()).' --allow-root'),
                'categories' => (int) shell_exec('wp term list category --format=count --path='.escapeshellarg($this->wordpressPath()).' --allow-root'),
                'tags' => (int) shell_exec('wp term list post_tag --format=count --path='.escapeshellarg($this->wordpressPath()).' --allow-root'),
                'users' => (int) shell_exec('wp user list --format=count --path='.escapeshellarg($this->wordpressPath()).' --allow-root'),
                'attachments' => (int) shell_exec('wp post list --post_type=attachment --format=count --path='.escapeshellarg($this->wordpressPath()).' --allow-root'),
            ],
            'media' => [
                'generated_files' => ['/work/media-fixtures/featured.jpg', '/work/media-fixtures/inline.png'],
                'featured_images' => [$seed['WORDPRESS_SEED_FEATURED_MEDIA_ID']],
                'inline_images' => [$seed['WORDPRESS_SEED_INLINE_MEDIA_ID']],
                'missing_files' => [],
            ],
            'sample_records' => [$seed],
        ];
    }

    private function laravelSummary(): array
    {
        return [
            'database' => [
                'records_by_table_or_model' => [
                    'wp_sites' => DB::table('wp_sites')->count(),
                    'wp_posts' => DB::table('wp_posts')->count(),
                    'wp_media_items' => DB::table('wp_media_items')->count(),
                    'wp_users' => DB::table('wp_users')->count(),
                ],
            ],
            'media' => [
                'synced_files' => MediaItem::query()->whereNotNull('local_path')->pluck('local_path')->all(),
                'missing_files' => MediaItem::query()->whereNotNull('local_path')->get()->filter(fn (MediaItem $media): bool => ! Storage::disk((string) $media->local_disk)->exists((string) $media->local_path))->pluck('local_path')->values()->all(),
                'records' => MediaItem::query()->get(['remote_id', 'source_url', 'local_path', 'file_size'])->toArray(),
            ],
            'sample_records' => Post::query()->limit(3)->get(['remote_id', 'raw_payload'])->toArray(),
        ];
    }

    private function failedAssertions(array $report): array
    {
        return array_values(array_filter($report['assertions'], static fn (array $assertion): bool => $assertion['status'] === 'failed'));
    }

    private function hasFailed(array $report, array $names): bool
    {
        foreach ($report['assertions'] as $assertion) {
            if (in_array($assertion['name'], $names, true) && $assertion['status'] === 'failed') {
                return true;
            }
        }

        return false;
    }

    private function writeReport(array $report): void
    {
        $path = getenv('INTEGRATION_REPORT_PATH') ?: base_path('../artifacts/integration-report.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $summary = [
            'Docker integration summary',
            'Status: '.$report['status'],
            'PHP: '.$report['environment']['php_version'],
            'Laravel: '.$report['environment']['laravel_version'],
            'WordPress: '.$report['environment']['wordpress_version'],
            'WordPress posts: '.data_get($report, 'wordpress.database.posts', 0),
            'Laravel posts: '.data_get($report, 'laravel.database.records_by_table_or_model.wp_posts', 0),
            'Laravel media: '.data_get($report, 'laravel.database.records_by_table_or_model.wp_media_items', 0),
            'Pull tested: '.(data_get($report, 'sync.pull.tested') ? 'yes' : 'no'),
            'Push tested: '.(data_get($report, 'sync.push.tested') ? 'yes' : 'no'),
            'Media files synced: '.count(data_get($report, 'laravel.media.synced_files', [])),
            'JSON report: '.$path,
        ];

        $summaryPath = getenv('INTEGRATION_SUMMARY_PATH') ?: base_path('../artifacts/integration-summary.txt');
        File::ensureDirectoryExists(dirname($summaryPath));
        File::put($summaryPath, implode(PHP_EOL, $summary).PHP_EOL);
    }

    private function wordpressUrl(): string
    {
        return (string) (getenv('WORDPRESS_URL') ?: 'http://app:8080');
    }

    private function wordpressPath(): string
    {
        return (string) (getenv('WORDPRESS_PATH') ?: '/work/wordpress');
    }
}
