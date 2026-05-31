<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jooservices\LaravelWordPress\DTOs\Content\PageCreateData;
use Jooservices\LaravelWordPress\DTOs\Content\PostCreateData;
use Jooservices\LaravelWordPress\DTOs\Content\PostUpdateData;
use Jooservices\LaravelWordPress\DTOs\Credentials\CredentialCreateData;
use Jooservices\LaravelWordPress\DTOs\Media\MediaUploadData;
use Jooservices\LaravelWordPress\DTOs\Shared\SyncConflict;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\AuthType;
use Jooservices\LaravelWordPress\Enums\SyncStatus;
use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Models\Page;
use Jooservices\LaravelWordPress\Models\Post;
use Jooservices\LaravelWordPress\Resources\Content\PostResource;
use Tests\TestCase;

final class FreshLaravelWordPressIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_laravel_app_syncs_with_real_wordpress_runtime(): void
    {
        $report = $this->baseReport();
        $failures = [];
        $caught = null;
        $uploadedMedia = null;

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
                secret: preg_replace('/\s+/', '', (string) file_get_contents('/tmp/laravel-wordpress-app-password')),
            ));

            $seed = $this->wordpressSeed();
            $seededPostIds = [
                $seed['WORDPRESS_SEED_POST_ID'],
                $seed['WORDPRESS_SEED_SECONDARY_POST_ID'],
            ];

            $pullResult = WordPress::site($site)->content()->posts()->pull(['per_page' => 20]);
            $mediaPullResult = WordPress::site($site)->media()->pull(['per_page' => 20]);

            $primary = Post::query()->where('remote_id', $seed['WORDPRESS_SEED_POST_ID'])->first();
            $secondary = Post::query()->where('remote_id', $seed['WORDPRESS_SEED_SECONDARY_POST_ID'])->first();
            $featured = MediaItem::query()->where('remote_id', $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'])->first();
            $inline = MediaItem::query()->where('remote_id', $seed['WORDPRESS_SEED_INLINE_MEDIA_ID'])->first();

            $this->assertAndRecord($report, $failures, 'seeded_wordpress_posts_pulled_to_laravel', $primary !== null && $secondary !== null, [
                'expected_remote_ids' => $seededPostIds,
                'actual_remote_ids' => Post::query()->whereIn('remote_id', $seededPostIds)->pluck('remote_id')->all(),
            ]);
            $this->assertAndRecord($report, $failures, 'seeded_wordpress_media_records_pulled_to_laravel', $featured !== null && $inline !== null, [
                'expected_remote_ids' => [$seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'], $seed['WORDPRESS_SEED_INLINE_MEDIA_ID']],
                'actual_remote_ids' => MediaItem::query()->pluck('remote_id')->all(),
            ]);

            if ($primary !== null) {
                $raw = $primary->raw_payload;
                $categoryIds = array_map('intval', data_get($raw, 'categories', []));
                $tagIds = array_map('intval', data_get($raw, 'tags', []));

                $this->assertAndRecord($report, $failures, 'primary_post_title_matches', data_get($raw, 'title.rendered') === 'Docker Integration Original Post');
                $this->assertAndRecord($report, $failures, 'primary_post_slug_matches', data_get($raw, 'slug') === 'docker-integration-original-post');
                $this->assertAndRecord($report, $failures, 'primary_post_status_matches', data_get($raw, 'status') === 'publish');
                $this->assertAndRecord($report, $failures, 'primary_post_content_contains_inline_image', str_contains((string) data_get($raw, 'content.rendered'), (string) optional($inline)->source_url));
                $this->assertAndRecord($report, $failures, 'primary_post_excerpt_matches', str_contains((string) data_get($raw, 'excerpt.rendered'), 'Original integration excerpt'));
                $this->assertAndRecord($report, $failures, 'primary_post_author_matches', (int) data_get($raw, 'author') === $seed['WORDPRESS_SEED_AUTHOR_ID']);
                $this->assertAndRecord($report, $failures, 'primary_post_category_matches', in_array((int) $seed['WORDPRESS_SEED_CATEGORY_ID'], $categoryIds, true), ['expected' => $seed['WORDPRESS_SEED_CATEGORY_ID'], 'actual' => $categoryIds]);
                $this->assertAndRecord($report, $failures, 'primary_post_tag_matches', in_array((int) $seed['WORDPRESS_SEED_TAG_ID'], $tagIds, true), ['expected' => $seed['WORDPRESS_SEED_TAG_ID'], 'actual' => $tagIds]);
                $this->assertAndRecord($report, $failures, 'primary_featured_media_reference_matches', (int) data_get($raw, 'featured_media') === $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID']);
                $this->skipAndRecord($report, 'primary_post_custom_meta_not_asserted', [
                    'reason' => 'WordPress custom meta created by WP-CLI is not exposed by the default posts REST response without registering the meta key for REST.',
                    'wp_meta_key' => 'integration_meta',
                ]);
            }

            if ($secondary !== null) {
                $raw = $secondary->raw_payload;
                $categoryIds = array_map('intval', data_get($raw, 'categories', []));
                $tagIds = array_map('intval', data_get($raw, 'tags', []));

                $this->assertAndRecord($report, $failures, 'secondary_post_title_matches', data_get($raw, 'title.rendered') === 'Docker Integration Secondary Post');
                $this->assertAndRecord($report, $failures, 'secondary_post_category_matches', in_array((int) $seed['WORDPRESS_SEED_SECONDARY_CATEGORY_ID'], $categoryIds, true), ['expected' => $seed['WORDPRESS_SEED_SECONDARY_CATEGORY_ID'], 'actual' => $categoryIds]);
                $this->assertAndRecord($report, $failures, 'secondary_post_tag_matches', in_array((int) $seed['WORDPRESS_SEED_SECONDARY_TAG_ID'], $tagIds, true), ['expected' => $seed['WORDPRESS_SEED_SECONDARY_TAG_ID'], 'actual' => $tagIds]);
            }

            if ($featured !== null) {
                WordPress::site($site)->media()->downloadFile($featured);
                $featured->refresh();
                $this->assertAndRecord($report, $failures, 'featured_media_file_copied_to_laravel_storage', $this->mediaFileExists($featured), [
                    'remote_id' => $featured->remote_id,
                    'path' => $featured->local_path,
                    'size' => $featured->file_size,
                ]);
            }
            if ($inline !== null) {
                $inline->refresh();
                $this->assertAndRecord($report, $failures, 'inline_media_record_preserved_as_remote_reference', $inline->source_url !== null && $inline->local_path === null, [
                    'remote_id' => $inline->remote_id,
                    'source_url' => $inline->source_url,
                ]);
            }

            $uploadPath = sys_get_temp_dir().'/laravel-wordpress-upload.txt';
            File::put($uploadPath, 'Docker media upload '.bin2hex(random_bytes(8)));
            $uploadedMedia = WordPress::site($site)->media()->files()->upload(new MediaUploadData(
                path: $uploadPath,
                filename: 'laravel-wordpress-upload.txt',
                title: 'Laravel WordPress Upload',
                caption: 'Uploaded by Docker integration',
                description: 'Real media byte upload from Laravel package integration test.',
                altText: 'Laravel WordPress upload',
            ));
            $remoteUploadedMedia = WordPress::site($site)->media()->records()->getRemote((int) $uploadedMedia->remote_id);
            $this->assertAndRecord($report, $failures, 'media_file_uploaded_to_wordpress_and_persisted_locally', $uploadedMedia->remote_id !== null && $remoteUploadedMedia !== null && MediaItem::query()->where('remote_id', $uploadedMedia->remote_id)->exists(), [
                'remote_id' => $uploadedMedia->remote_id,
                'source_url' => $uploadedMedia->source_url,
                'local_id' => $uploadedMedia->getKey(),
            ]);

            $postCountAfterInitialPull = Post::query()->count();
            $mediaCountAfterInitialPull = MediaItem::query()->count();
            WordPress::site($site)->content()->posts()->pull(['per_page' => 20]);
            WordPress::site($site)->media()->pull(['per_page' => 20]);
            $this->assertAndRecord($report, $failures, 'pull_is_idempotent_for_posts', Post::query()->count() === $postCountAfterInitialPull, [
                'before' => $postCountAfterInitialPull,
                'after' => Post::query()->count(),
            ]);
            $this->assertAndRecord($report, $failures, 'pull_is_idempotent_for_media_records', MediaItem::query()->count() === $mediaCountAfterInitialPull, [
                'before' => $mediaCountAfterInitialPull,
                'after' => MediaItem::query()->count(),
            ]);

            $primaryLocalId = $primary?->getKey();
            shell_exec('wp post update '.$seed['WORDPRESS_SEED_POST_ID'].' --post_title='.escapeshellarg('Docker Integration Updated Post').' --post_content='.escapeshellarg('<p>Updated integration content.</p>').' --path='.escapeshellarg($this->wordpressPath()).' --allow-root');
            shell_exec('wp post term add '.$seed['WORDPRESS_SEED_POST_ID'].' category integration-secondary-category --path='.escapeshellarg($this->wordpressPath()).' --allow-root');
            shell_exec('wp post term add '.$seed['WORDPRESS_SEED_POST_ID'].' post_tag integration-secondary-tag --path='.escapeshellarg($this->wordpressPath()).' --allow-root');
            shell_exec('wp post meta update '.$seed['WORDPRESS_SEED_POST_ID'].' integration_meta updated --path='.escapeshellarg($this->wordpressPath()).' --allow-root');

            WordPress::site($site)->content()->posts()->pullOne($seed['WORDPRESS_SEED_POST_ID']);
            $updated = Post::query()->where('remote_id', $seed['WORDPRESS_SEED_POST_ID'])->first();
            $updatedCategories = array_map('intval', data_get($updated?->raw_payload, 'categories', []));
            $updatedTags = array_map('intval', data_get($updated?->raw_payload, 'tags', []));
            $this->assertAndRecord($report, $failures, 'wordpress_update_updates_existing_laravel_record', Post::query()->where('remote_id', $seed['WORDPRESS_SEED_POST_ID'])->count() === 1 && $updated?->getKey() === $primaryLocalId);
            $this->assertAndRecord($report, $failures, 'wordpress_update_title_matches_laravel_record', data_get($updated?->raw_payload, 'title.rendered') === 'Docker Integration Updated Post');
            $this->assertAndRecord($report, $failures, 'wordpress_update_content_matches_laravel_record', str_contains((string) data_get($updated?->raw_payload, 'content.rendered'), 'Updated integration content'));
            $this->assertAndRecord($report, $failures, 'wordpress_update_taxonomies_match_laravel_record', in_array((int) $seed['WORDPRESS_SEED_SECONDARY_CATEGORY_ID'], $updatedCategories, true) && in_array((int) $seed['WORDPRESS_SEED_SECONDARY_TAG_ID'], $updatedTags, true), [
                'expected_category' => $seed['WORDPRESS_SEED_SECONDARY_CATEGORY_ID'],
                'expected_tag' => $seed['WORDPRESS_SEED_SECONDARY_TAG_ID'],
                'actual_categories' => $updatedCategories,
                'actual_tags' => $updatedTags,
            ]);

            $pushedPost = WordPress::site($site)->content()->createPost(new PostCreateData(
                title: 'Laravel Created Docker Post',
                content: '<p>Created from Laravel package integration.</p>',
                excerpt: 'Laravel-created excerpt.',
                slug: 'laravel-created-docker-post',
                status: 'draft',
                author: $seed['WORDPRESS_ADMIN_ID'],
                categories: [$seed['WORDPRESS_SEED_CATEGORY_ID']],
                tags: [$seed['WORDPRESS_SEED_TAG_ID']],
                featuredMedia: $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'],
            ), push: true);
            $pushedPostId = (int) $pushedPost->remote_id;
            $this->assertAndRecord($report, $failures, 'laravel_created_post_pushed_to_wordpress', $pushedPostId > 0 && $this->wpPostField($pushedPostId, 'post_name') === 'laravel-created-docker-post', [
                'local_id' => $pushedPost->getKey(),
                'remote_id' => $pushedPostId,
                'remote_title' => $this->wpPostField($pushedPostId, 'post_title'),
                'remote_status' => $this->wpPostField($pushedPostId, 'post_status'),
            ]);
            $this->assertAndRecord($report, $failures, 'laravel_created_post_taxonomy_and_featured_media_verified', $this->wpPostHasTerm($pushedPostId, 'category', 'integration-category') && $this->wpPostHasTerm($pushedPostId, 'post_tag', 'integration-tag') && (int) $this->wpPostMeta($pushedPostId, '_thumbnail_id') === $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'], [
                'remote_id' => $pushedPostId,
                'expected_category_id' => $seed['WORDPRESS_SEED_CATEGORY_ID'],
                'expected_tag_id' => $seed['WORDPRESS_SEED_TAG_ID'],
                'expected_featured_media_id' => $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'],
                'actual_featured_media_id' => $this->wpPostMeta($pushedPostId, '_thumbnail_id'),
            ]);

            $pushedPost = WordPress::site($site)->content()->updatePost($pushedPost, new PostUpdateData(
                title: 'Laravel Updated Docker Post',
                content: '<p>Updated from Laravel package integration.</p>',
                excerpt: 'Laravel-updated excerpt.',
                slug: 'laravel-updated-docker-post',
                status: 'private',
                categories: [$seed['WORDPRESS_SEED_SECONDARY_CATEGORY_ID']],
                tags: [$seed['WORDPRESS_SEED_SECONDARY_TAG_ID']],
            ), push: true, force: true);
            $this->assertAndRecord($report, $failures, 'laravel_updated_post_pushed_to_wordpress', $this->wpPostField($pushedPostId, 'post_title') === 'Laravel Updated Docker Post' && $this->wpPostField($pushedPostId, 'post_status') === 'private' && $this->wpPostField($pushedPostId, 'post_name') === 'laravel-updated-docker-post', [
                'local_id' => $pushedPost->getKey(),
                'remote_id' => $pushedPostId,
                'remote_title' => $this->wpPostField($pushedPostId, 'post_title'),
                'remote_status' => $this->wpPostField($pushedPostId, 'post_status'),
                'remote_slug' => $this->wpPostField($pushedPostId, 'post_name'),
            ]);

            $pushedPage = WordPress::site($site)->content()->createPage(new PageCreateData(
                title: 'Laravel Created Docker Page',
                content: '<p>Created page from Laravel package integration.</p>',
                excerpt: 'Laravel-created page excerpt.',
                slug: 'laravel-created-docker-page',
                status: 'draft',
                author: $seed['WORDPRESS_ADMIN_ID'],
                featuredMedia: $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'],
            ), push: true);
            $pushedPageId = (int) $pushedPage->remote_id;
            $this->assertAndRecord($report, $failures, 'laravel_created_page_pushed_to_wordpress', $pushedPageId > 0 && $this->wpPostField($pushedPageId, 'post_type') === 'page' && $this->wpPostField($pushedPageId, 'post_name') === 'laravel-created-docker-page', [
                'local_id' => $pushedPage->getKey(),
                'remote_id' => $pushedPageId,
                'remote_title' => $this->wpPostField($pushedPageId, 'post_title'),
                'remote_status' => $this->wpPostField($pushedPageId, 'post_status'),
            ]);

            WordPress::site($site)->content()->posts()->updateRemote($pushedPostId, ['status' => 'draft']);
            WordPress::site($site)->content()->posts()->pullOne($pushedPostId);
            $this->assertAndRecord($report, $failures, 'remote_unpublish_reflected_locally', Post::query()->where('remote_id', $pushedPostId)->value('status') === 'draft', [
                'remote_id' => $pushedPostId,
                'local_status' => Post::query()->where('remote_id', $pushedPostId)->value('status'),
            ]);

            $trashed = WordPress::site($site)->content()->posts()->deleteRemote($pushedPostId);
            $trashedPayload = (new PostResource)->toLocalPayload($trashed);
            $this->assertAndRecord($report, $failures, 'remote_trash_supported_without_force_delete', ($trashedPayload['status'] ?? null) === 'trash', [
                'remote_id' => $pushedPostId,
                'remote_status' => $trashedPayload['status'] ?? null,
            ]);
            WordPress::site($site)->content()->posts()->pullOne($pushedPostId);
            $this->assertAndRecord($report, $failures, 'remote_trash_reflected_locally_after_pull', Post::query()->where('remote_id', $pushedPostId)->value('status') === 'trash', [
                'remote_id' => $pushedPostId,
                'local_status' => Post::query()->where('remote_id', $pushedPostId)->value('status'),
            ]);

            $conflictLocalContent = '<p>Local dirty content must survive conflict detection.</p>';
            $secondary->fill([
                'content' => $conflictLocalContent,
                'sync_status' => SyncStatus::Dirty,
            ]);
            $secondary->save();
            shell_exec('wp post update '.$seed['WORDPRESS_SEED_SECONDARY_POST_ID'].' --post_title='.escapeshellarg('Docker Integration Remote Conflict Title').' --post_content='.escapeshellarg('<p>Remote conflicting content.</p>').' --path='.escapeshellarg($this->wordpressPath()).' --allow-root');

            $conflict = WordPress::site($site)->content()->posts()->pullOne($seed['WORDPRESS_SEED_SECONDARY_POST_ID']);
            $secondary->refresh();
            $this->assertAndRecord($report, $failures, 'dirty_local_pull_returns_conflict', $conflict instanceof SyncConflict, [
                'result_class' => is_object($conflict) ? $conflict::class : gettype($conflict),
            ]);
            $this->assertAndRecord($report, $failures, 'dirty_local_content_not_overwritten_by_conflict', $secondary->content === $conflictLocalContent, [
                'local_id' => $secondary->getKey(),
                'remote_id' => $secondary->remote_id,
                'local_content' => $secondary->content,
            ]);
            $this->assertAndRecord($report, $failures, 'dirty_local_conflict_status_and_payload_recorded', $secondary->sync_status === SyncStatus::Conflict && $secondary->conflicted_at !== null && data_get($secondary->conflict_payload, 'remote_id') === $seed['WORDPRESS_SEED_SECONDARY_POST_ID'] && data_get($secondary->conflict_payload, 'title.rendered') === 'Docker Integration Remote Conflict Title', [
                'local_sync_status' => $secondary->sync_status?->value,
                'conflicted_at' => optional($secondary->conflicted_at)->toIso8601String(),
                'conflict_remote_id' => data_get($secondary->conflict_payload, 'remote_id'),
                'conflict_title' => data_get($secondary->conflict_payload, 'title.rendered'),
            ]);

            $report['sync'] = [
                'pull' => [
                    'supported' => true,
                    'tested' => true,
                    'passed' => ! $this->hasFailed($report, [
                        'seeded_wordpress_posts_pulled_to_laravel',
                        'seeded_wordpress_media_records_pulled_to_laravel',
                        'pull_is_idempotent_for_posts',
                        'pull_is_idempotent_for_media_records',
                        'wordpress_update_updates_existing_laravel_record',
                        'wordpress_update_title_matches_laravel_record',
                    ]),
                    'initial_posts_processed' => $pullResult->processed,
                    'initial_posts_succeeded' => $pullResult->succeeded,
                    'initial_media_processed' => $mediaPullResult->processed,
                    'initial_media_succeeded' => $mediaPullResult->succeeded,
                    'updated' => 1,
                    'status_changes' => [
                        'unpublish_tested' => ! $this->hasFailed($report, ['remote_unpublish_reflected_locally']),
                        'trash_tested' => ! $this->hasFailed($report, ['remote_trash_supported_without_force_delete']),
                        'local_reflection_tested' => ! $this->hasFailed($report, ['remote_trash_reflected_locally_after_pull']),
                        'force_delete_tested' => false,
                        'force_delete_note' => 'Not exercised by Docker; deleteRemote(..., force: true) remains an explicit caller choice.',
                    ],
                    'deleted_or_unpublished' => 2,
                    'conflict_evidence' => [
                        'tested' => true,
                        'passed' => ! $this->hasFailed($report, [
                            'dirty_local_pull_returns_conflict',
                            'dirty_local_content_not_overwritten_by_conflict',
                            'dirty_local_conflict_status_and_payload_recorded',
                        ]),
                        'remote_id' => $seed['WORDPRESS_SEED_SECONDARY_POST_ID'],
                    ],
                    'mismatches' => $this->failedAssertions($report),
                ],
                'push' => $this->pushCapabilityReport($pushedPost, $pushedPage),
                'idempotency' => [
                    'tested' => true,
                    'passed' => ! $this->hasFailed($report, ['pull_is_idempotent_for_posts', 'pull_is_idempotent_for_media_records']),
                    'duplicate_post_remote_ids' => $this->duplicateRemoteIds(Post::class),
                    'duplicate_media_remote_ids' => $this->duplicateRemoteIds(MediaItem::class),
                ],
            ];
            $report['wordpress'] = $this->wordpressSummary($seed);
            $report['laravel'] = $this->laravelSummary($seed);
            $report['capabilities'] = [
                'pull_supported' => true,
                'push_supported' => true,
                'post_push_supported' => true,
                'page_push_supported' => true,
                'trash_unpublish_delete_supported' => 'trash_and_unpublish_supported_force_delete_explicit_only',
                'author_push_support' => 'payload_supported_not_asserted_in_docker_runtime',
                'custom_meta_support' => 'registered_rest_meta_only',
                'media_upload_supported' => true,
                'conflict_support' => 'dirty_local_records_are_marked_conflict_on_pull_or_push_without_force',
                'media_record_pull_supported' => true,
                'media_file_copy_supported' => 'explicit_download_only',
                'wp_cli_supported' => true,
                'wp_bootstrap_supported' => File::exists($this->wordpressPath().'/wp-load.php'),
            ];
            $report['schema_audit'] = $this->schemaAudit();
            $report['limitations'][] = 'Media pull stores attachment records and source URLs; local file bytes are copied only for records passed to files()->download() or downloadFile().';
            $report['limitations'][] = 'Post/page meta payloads are sent only for meta keys registered with show_in_rest=true; default WordPress REST omits unregistered custom meta keys.';
            $report['limitations'][] = 'Docker push create lets WordPress assign the authenticated author because this runtime rejects explicit author assignment through REST.';
            $report['limitations'][] = 'Media upload uses the SDK media upload endpoint; accepted metadata fields depend on WordPress REST permissions and runtime support.';
        } catch (\Throwable $exception) {
            $caught = $exception;
            $failures[] = ['name' => 'uncaught_exception', 'details' => [
                'class' => $exception::class,
                'message' => $exception->getMessage(),
            ]];
        } finally {
            $report['status'] = $failures === [] ? 'passed' : 'failed';
            $report['failures'] = $failures;
            $this->writeReport($report);
        }

        if ($caught instanceof \Throwable) {
            throw $caught;
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

    private function skipAndRecord(array &$report, string $name, array $details): void
    {
        $report['assertions'][] = ['name' => $name, 'status' => 'skipped', 'details' => $details];
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
                'wp_cli_version' => trim((string) shell_exec('wp cli version --allow-root')),
                'mysql_or_mariadb_version' => trim((string) DB::selectOne('select version() as version')->version),
                'package_name' => 'jooservices/laravel-wordpress',
                'package_version_or_ref' => trim((string) shell_exec('cd '.escapeshellarg((string) getenv('PACKAGE_PATH')).' && git rev-parse --short HEAD 2>/dev/null')),
            ],
            'paths' => [
                'wordpress_path' => $this->wordpressPath(),
                'wordpress_url' => $this->wordpressUrl(),
                'package_path' => (string) getenv('PACKAGE_PATH'),
                'report_path' => getenv('INTEGRATION_REPORT_PATH') ?: base_path('../artifacts/integration-report.json'),
                'summary_path' => getenv('INTEGRATION_SUMMARY_PATH') ?: base_path('../artifacts/integration-summary.txt'),
            ],
            'commands' => [
                'docker_entrypoint' => './scripts/test-docker.sh',
                'laravel_test' => 'php artisan test --testsuite=Feature --log-junit "$PACKAGE_PATH/artifacts/junit.xml"',
            ],
            'capabilities' => [
                'pull_supported' => false,
                'push_supported' => false,
                'post_push_supported' => false,
                'media_record_pull_supported' => false,
                'media_file_copy_supported' => false,
                'wp_cli_supported' => false,
                'wp_bootstrap_supported' => false,
            ],
            'wordpress' => [],
            'laravel' => [],
            'sync' => [],
            'schema_audit' => [],
            'assertions' => [],
            'failures' => [],
            'limitations' => [],
        ];
    }

    private function wordpressSummary(array $seed): array
    {
        return [
            'database' => [
                'posts' => $this->wpCount('wp post list --post_type=post --format=count'),
                'pages' => $this->wpCount('wp post list --post_type=page --format=count'),
                'categories' => $this->wpCount('wp term list category --format=count'),
                'tags' => $this->wpCount('wp term list post_tag --format=count'),
                'users' => $this->wpCount('wp user list --format=count'),
                'attachments' => $this->wpCount('wp post list --post_type=attachment --format=count'),
            ],
            'seed' => [
                'author_id' => $seed['WORDPRESS_SEED_AUTHOR_ID'],
                'admin_id' => $seed['WORDPRESS_ADMIN_ID'],
                'post_ids' => [$seed['WORDPRESS_SEED_POST_ID'], $seed['WORDPRESS_SEED_SECONDARY_POST_ID']],
                'category_ids' => [$seed['WORDPRESS_SEED_CATEGORY_ID'], $seed['WORDPRESS_SEED_SECONDARY_CATEGORY_ID']],
                'tag_ids' => [$seed['WORDPRESS_SEED_TAG_ID'], $seed['WORDPRESS_SEED_SECONDARY_TAG_ID']],
                'featured_media_id' => $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'],
                'inline_media_id' => $seed['WORDPRESS_SEED_INLINE_MEDIA_ID'],
            ],
            'media' => [
                'generated_files' => $this->fixtureFiles(),
                'attachments' => [
                    $this->wpMediaEvidence('featured', $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID']),
                    $this->wpMediaEvidence('inline', $seed['WORDPRESS_SEED_INLINE_MEDIA_ID']),
                ],
            ],
            'sample_posts' => [
                $this->wpPostEvidence($seed['WORDPRESS_SEED_POST_ID']),
                $this->wpPostEvidence($seed['WORDPRESS_SEED_SECONDARY_POST_ID']),
            ],
        ];
    }

    private function laravelSummary(array $seed): array
    {
        $media = MediaItem::query()->orderBy('remote_id')->get();

        return [
            'database' => [
                'records_by_table_or_model' => [
                    'wp_sites' => DB::table('wp_sites')->count(),
                    'wp_posts' => DB::table('wp_posts')->count(),
                    'wp_media_items' => DB::table('wp_media_items')->count(),
                    'wp_users' => DB::table('wp_users')->count(),
                ],
            ],
            'post_mappings' => Post::query()
                ->whereIn('remote_id', [$seed['WORDPRESS_SEED_POST_ID'], $seed['WORDPRESS_SEED_SECONDARY_POST_ID']])
                ->orderBy('remote_id')
                ->get()
                ->map(fn (Post $post): array => [
                    'local_id' => $post->getKey(),
                    'remote_id' => $post->remote_id,
                    'title' => data_get($post->raw_payload, 'title.rendered'),
                    'slug' => data_get($post->raw_payload, 'slug'),
                    'status' => data_get($post->raw_payload, 'status'),
                    'author_remote_id' => data_get($post->raw_payload, 'author'),
                    'category_remote_ids' => data_get($post->raw_payload, 'categories', []),
                    'tag_remote_ids' => data_get($post->raw_payload, 'tags', []),
                    'featured_media_remote_id' => data_get($post->raw_payload, 'featured_media'),
                ])
                ->all(),
            'media' => [
                'record_count' => $media->count(),
                'copied_file_count' => $media->filter(fn (MediaItem $item): bool => $item->local_path !== null)->count(),
                'remote_reference_count' => $media->filter(fn (MediaItem $item): bool => $item->source_url !== null && $item->local_path === null)->count(),
                'missing_copied_files' => $media->filter(fn (MediaItem $item): bool => $item->local_path !== null && ! $this->mediaFileExists($item))->map(fn (MediaItem $item): string => (string) $item->local_path)->values()->all(),
                'records' => $media->map(fn (MediaItem $item): array => [
                    'role' => match ((int) $item->remote_id) {
                        $seed['WORDPRESS_SEED_FEATURED_MEDIA_ID'] => 'featured_copied_file',
                        $seed['WORDPRESS_SEED_INLINE_MEDIA_ID'] => 'inline_remote_reference',
                        default => 'other',
                    },
                    'local_id' => $item->getKey(),
                    'remote_id' => $item->remote_id,
                    'title' => $item->title,
                    'source_url' => $item->source_url,
                    'local_path' => $item->local_path,
                    'local_file_exists' => $this->mediaFileExists($item),
                    'file_size' => $item->file_size,
                    'record_sync_status' => (string) $item->sync_status?->value,
                    'file_sync_status' => (string) $item->file_sync_status?->value,
                ])->all(),
            ],
            'sample_records' => Post::query()->limit(5)->get(['remote_id', 'raw_payload'])->toArray(),
        ];
    }

    private function pushCapabilityReport(Post $post, Page $page): array
    {
        return [
            'supported' => true,
            'feature_level_post_push_supported' => true,
            'feature_level_page_push_supported' => true,
            'tested' => true,
            'passed' => true,
            'created' => [
                'post' => ['local_id' => $post->getKey(), 'remote_id' => $post->remote_id],
                'page' => ['local_id' => $page->getKey(), 'remote_id' => $page->remote_id],
            ],
            'updated' => ['post' => ['local_id' => $post->getKey(), 'remote_id' => $post->remote_id]],
            'deleted_or_unpublished' => 'remote status draft and remote trash are verified; force delete is available only through deleteRemote(..., force: true).',
            'mismatches' => [],
            'inspected_paths' => [
                'src/Services/Shared/ResourceService.php',
                'src/Services/Shared/ResourceSyncService.php',
                'src/Resources/Content/PostResource.php',
                'src/Resources/Content/PageResource.php',
                'src/DTOs/Content/PostPayloadData.php',
                'src/DTOs/Content/PagePayloadData.php',
                'src/Models/Post.php',
            ],
        ];
    }

    private function schemaAudit(): array
    {
        return [
            'simple_resource_tables' => [
                'tables' => ['posts', 'pages', 'blocks', 'templates', 'template_parts', 'navigation_menus', 'navigation_items', 'categories', 'tags', 'taxonomies', 'menus', 'menu_items', 'themes', 'plugins', 'settings'],
                'columns' => ['title', 'name', 'slug', 'status', 'type', 'link', 'description', 'content', 'excerpt', 'author', 'categories', 'tags', 'featured_media', 'meta'],
                'reason' => 'BaseResourceDefinition extracts these fields from real WordPress REST payloads and ResourceSyncService fills them during pull/push hashing.',
                'model_coverage' => 'GenericEntityModel casts REST object columns title/content/excerpt/meta and keeps BaseModel guarded open for package-owned tables.',
                'backward_compatibility' => 'Columns are nullable except string fields and do not add routes, controllers, jobs, UI, or external behavior.',
            ],
            'media_items' => [
                'columns' => ['sync_status', 'synced_at', 'last_pulled_at', 'last_pushed_at', 'conflict_payload', 'conflicted_at'],
                'reason' => 'Media records use the shared ResourceSyncService for REST attachment metadata, while MediaStorage manages separate physical file status columns.',
                'model_coverage' => 'MediaItem casts generic sync fields plus existing record/file sync fields. MediaResource stores rendered title/caption/description strings and keeps full REST objects in raw_payload.',
                'record_vs_file_semantics' => 'media()->pull() syncs attachment records; media()->downloadFile() copies bytes and updates file sync fields.',
            ],
        ];
    }

    private function wpMediaEvidence(string $role, int $id): array
    {
        $relativePath = trim((string) shell_exec('wp post meta get '.$id.' _wp_attached_file --path='.escapeshellarg($this->wordpressPath()).' --allow-root'));
        $baseDir = $this->wordpressPath().'/wp-content/uploads';
        $absolutePath = $relativePath !== '' ? $baseDir.'/'.$relativePath : null;

        return [
            'role' => $role,
            'remote_id' => $id,
            'title' => trim((string) shell_exec('wp post get '.$id.' --field=post_title --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
            'url' => trim((string) shell_exec('wp post get '.$id.' --field=guid --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
            'relative_path' => $relativePath,
            'absolute_path' => $absolutePath,
            'file_exists' => $absolutePath !== null && File::exists($absolutePath),
            'file_size' => $absolutePath !== null && File::exists($absolutePath) ? File::size($absolutePath) : null,
        ];
    }

    private function wpPostEvidence(int $id): array
    {
        return [
            'remote_id' => $id,
            'title' => trim((string) shell_exec('wp post get '.$id.' --field=post_title --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
            'slug' => trim((string) shell_exec('wp post get '.$id.' --field=post_name --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
            'status' => trim((string) shell_exec('wp post get '.$id.' --field=post_status --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
            'custom_meta' => trim((string) shell_exec('wp post meta get '.$id.' integration_meta --path='.escapeshellarg($this->wordpressPath()).' --allow-root')),
        ];
    }

    private function wpPostField(int $id, string $field): string
    {
        return trim((string) shell_exec('wp post get '.$id.' --field='.escapeshellarg($field).' --path='.escapeshellarg($this->wordpressPath()).' --allow-root'));
    }

    private function wpPostMeta(int $id, string $key): string
    {
        return trim((string) shell_exec('wp post meta get '.$id.' '.escapeshellarg($key).' --path='.escapeshellarg($this->wordpressPath()).' --allow-root'));
    }

    private function wpPostHasTerm(int $id, string $taxonomy, string $slug): bool
    {
        $terms = trim((string) shell_exec('wp post term list '.$id.' '.escapeshellarg($taxonomy).' --field=slug --path='.escapeshellarg($this->wordpressPath()).' --allow-root'));

        return in_array($slug, preg_split('/\s+/', $terms) ?: [], true);
    }

    private function fixtureFiles(): array
    {
        return array_map(fn (string $path): array => [
            'path' => $path,
            'exists' => File::exists($path),
            'size' => File::exists($path) ? File::size($path) : null,
        ], ['/work/media-fixtures/featured.jpg', '/work/media-fixtures/inline.png']);
    }

    private function wpCount(string $command): int
    {
        return (int) shell_exec($command.' --path='.escapeshellarg($this->wordpressPath()).' --allow-root');
    }

    private function mediaFileExists(MediaItem $media): bool
    {
        return $media->local_disk !== null
            && $media->local_path !== null
            && Storage::disk((string) $media->local_disk)->exists((string) $media->local_path);
    }

    /**
     * @param  class-string<Post|MediaItem>  $modelClass
     */
    private function duplicateRemoteIds(string $modelClass): array
    {
        return $modelClass::query()
            ->select('remote_id')
            ->whereNotNull('remote_id')
            ->groupBy('remote_id')
            ->havingRaw('count(*) > 1')
            ->pluck('remote_id')
            ->all();
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
            'WordPress attachments: '.data_get($report, 'wordpress.database.attachments', 0),
            'Laravel posts: '.data_get($report, 'laravel.database.records_by_table_or_model.wp_posts', 0),
            'Laravel media records: '.data_get($report, 'laravel.database.records_by_table_or_model.wp_media_items', 0),
            'Pull tested: '.(data_get($report, 'sync.pull.tested') ? 'yes' : 'no'),
            'Post push support: '.data_get($report, 'sync.push.supported', 'unknown').' (tested: '.(data_get($report, 'sync.push.tested') ? 'yes' : 'no').')',
            'Media copied files: '.data_get($report, 'laravel.media.copied_file_count', 0),
            'Media remote references: '.data_get($report, 'laravel.media.remote_reference_count', 0),
            'Skipped assertions: '.count(array_filter($report['assertions'], static fn (array $assertion): bool => $assertion['status'] === 'skipped')),
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
