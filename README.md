# Laravel WordPress

Laravel service package for WordPress local/remote CRUD, sync, and media/file management.

This package provides a service layer only: no routes, controllers, jobs, queues, events, listeners, audit log, or UI. It stores local WordPress state in package-owned tables, talks to remote WordPress through `jooservices/wordpress-sdk`, and keeps sync state explicit.

## Install

```bash
composer require jooservices/laravel-wordpress
php artisan vendor:publish --tag=laravel-wordpress-config
php artisan vendor:publish --tag=migrations
php artisan migrate
```

## Usage

```php
use Jooservices\LaravelWordPress\DTOs\Credentials\CredentialCreateData;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\AuthType;
use Jooservices\LaravelWordPress\Facades\WordPress;

$site = WordPress::sites()->create(new SiteCreateData('Production', 'https://example.com'));

WordPress::credentials()->createForSite($site, new CredentialCreateData(
    name: 'Default',
    authType: AuthType::ApplicationPassword,
    username: 'admin',
    secret: env('WP_APP_PASSWORD'),
));

WordPress::site($site)->users()->users()->pull();
WordPress::site($site)->content()->posts()->pull();
WordPress::site($site)->media()->downloadFile($media);
```

Grouped services expose resources through `ResourceDefinition` adapters. The sync layer never auto-merges conflicts and never deletes physical files unless `deleteLocalFile()` is called.

## Testing

```bash
composer validate --strict
composer install
composer lint
composer test
composer test:unit
composer test:feature
composer test:integration
composer test:real
composer quality
```

### Docker Real Integration Test

The Docker workflow installs a fresh Laravel app, installs this package into it with a Composer path repository, installs WordPress with WP-CLI, seeds real WordPress records and media files, runs Laravel package migrations, and executes a PHPUnit smoke test against the real services.

Prerequisites: Docker and Docker Compose.

```bash
./scripts/test-docker.sh
```

The workflow validates package discovery, config loading, package migrations, WP-CLI availability, WordPress installation, real WordPress post/media generation, WordPress-to-Laravel pull sync, media record pull, explicit media file download, update handling, and idempotent repeated pull behavior. It writes:

```text
artifacts/integration-report.json
artifacts/integration-summary.txt
artifacts/junit.xml
```

The JSON report includes environment versions, executed paths/commands, package capabilities, schema audit notes, WordPress record counts, Laravel record counts, media record/file evidence, pull/push/idempotency results, assertions, skipped assertions, failures, and limitations. Media is reported in three separate buckets: WordPress attachments, Laravel media records, and local copied files. `media()->pull()` stores attachment records and source URLs; local bytes are copied only when `downloadFile()` is called.

Push services exist as generic infrastructure through `ResourceService`, but the Docker suite reports post push as partial and untested at the feature level. It does not claim Laravel-originated post push support until a post-specific DTO/mapper proves author, taxonomy, custom meta, featured media, and rendered content semantics against real WordPress.

The package migration includes nullable common REST columns (`title`, `name`, `slug`, `status`, `type`, `link`, `description`, `content`, `excerpt`, `meta`) on generic resource tables because `BaseResourceDefinition` extracts those fields from real WordPress REST payloads and `ResourceSyncService` fills them during synchronization. `media_items` also includes generic record sync columns used by the shared sync service, while existing media file sync columns continue to represent physical file copy state.

Useful defaults can be overridden with environment variables such as `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `WORDPRESS_PATH`, `WORDPRESS_URL`, `LARAVEL_APP_PATH`, and `PACKAGE_PATH`. If the run fails, inspect `artifacts/integration-report.json`, `artifacts/integration-summary.txt`, and the Docker output; the WordPress PHP server log is written inside the app container at `/tmp/wordpress-test-server.log`.
