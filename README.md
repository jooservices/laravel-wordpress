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

The workflow validates package discovery, config loading, package migrations, WP-CLI availability, WordPress installation, real WordPress post/media generation, WordPress-to-Laravel pull sync, media record pull and file download, update handling, and idempotent repeated pull behavior. It writes:

```text
artifacts/integration-report.json
artifacts/integration-summary.txt
artifacts/junit.xml
```

The JSON report includes environment versions, package capabilities, WordPress record counts, Laravel record counts, media file checks, pull/push/idempotency results, assertions, failures, and limitations. Push services exist in the package, but the Docker smoke suite currently reports push as untested because post-specific Laravel-originated mapping for author, taxonomy, meta, featured media, and rendered content is not represented by a dedicated DTO.

Useful defaults can be overridden with environment variables such as `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `WORDPRESS_PATH`, `WORDPRESS_URL`, `LARAVEL_APP_PATH`, and `PACKAGE_PATH`. If the run fails, inspect `artifacts/integration-report.json`, `artifacts/integration-summary.txt`, and the Docker output; the WordPress PHP server log is written inside the app container at `/tmp/wordpress-test-server.log`.
