# Laravel WordPress

Laravel service package for WordPress local/remote CRUD, sync, and media record/file
management.

This package is a service layer only: no routes, controllers, jobs, queues, events,
listeners, audit log, sync history, background processing, or UI. It stores local WordPress
state in package-owned tables, talks to remote WordPress through `jooservices/wordpress-sdk`,
and keeps sync state explicit.

## Install

```bash
composer require jooservices/laravel-wordpress
php artisan vendor:publish --tag=laravel-wordpress-config
php artisan vendor:publish --tag=migrations
php artisan migrate
```

See [installation](docs/01-getting-started/installation.md) and
[configuration](docs/01-getting-started/configuration.md) for setup notes.

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
WordPress::site($site)->media()->records()->pull();
WordPress::site($site)->media()->files()->download($media);
```

## Architecture Overview

The public flow is:

```text
WordPress facade -> Manager -> SiteContext -> grouped service -> ResourceService
    -> ResourceDefinition -> ResourceLocalService / ResourceRemoteService / ResourceSyncService
```

Grouped services expose resource families while the shared resource layer owns common local,
remote, and sync behavior. `ResourceDefinition` classes adapt WordPress REST resources to local
tables, Eloquent models, remote SDK services, and payload mapping. Details are in
[architecture](docs/00-architecture/service-architecture.md).

## Service Map

| Entry point | Purpose |
| --- | --- |
| `WordPress::sites()` | Site records |
| `WordPress::credentials()` | Site credentials |
| `WordPress::site($site)->content()` | Posts, pages, revisions, autosaves |
| `WordPress::site($site)->media()->records()` | Media attachment record local/remote CRUD and record sync |
| `WordPress::site($site)->media()->files()` | Media file upload, download, and local file deletion |
| `WordPress::site($site)->taxonomy()` | Taxonomies and terms |
| `WordPress::site($site)->users()` | Users |
| `WordPress::site($site)->applicationPasswords()` | Application passwords |
| `WordPress::site($site)->comments()` | Comments |
| `WordPress::site($site)->system()` | Settings, options, types, statuses, themes, plugins |
| `WordPress::site($site)->editor()` | Blocks, patterns, templates, global styles |
| `WordPress::site($site)->navigation()` | Navigation, menus, menu items, locations |
| `WordPress::site($site)->widgets()` | Sidebars, widgets, widget types |
| `WordPress::site($site)->remoteResources()` | Dynamic remote resource access |

## Resource Capability Matrix

| Resource area | Status |
| --- | --- |
| Posts and pages | Feature-level tested for pull, local create/update, push, taxonomy IDs, featured media, unpublish, trash reflection, and conflict detection |
| Media records | Feature-level tested for attachment record pull and source URL persistence |
| Media file download | Feature-level tested through explicit `downloadFile()` local byte copy |
| Media upload | Supported through `media()->files()->upload(...)` / `uploadFile(...)` using `jooservices/wordpress-sdk` real Media Library upload |
| Generic resource tables and definitions | Available for remote/local/sync operations through the shared resource layer, but not all are feature-complete |
| Author assignment | Runtime-dependent; WordPress REST permissions can override requested authors |
| Custom REST meta | Runtime-dependent; keys must be registered in WordPress with `show_in_rest=true` |

## Sync Lifecycle

`pull()` reads remote REST payloads, maps them through a `ResourceDefinition`, stores local
records, and updates explicit sync hashes and timestamps. `push()` creates or updates remote
records from local models. `sync()` currently aliases pull behavior on shared resources.

Sync state uses local and remote hashes, `sync_status`, `synced_at`, `last_pulled_at`,
`last_pushed_at`, `conflict_payload`, and `conflicted_at`. See [sync](docs/02-user-guide/sync.md).

## Conflict Handling

The package does not auto-merge conflicts. Dirty local records are not silently overwritten by a
remote pull, and remote changes are not silently overwritten by a local push unless force/local
resolution is intentionally used. Use:

- `resolveConflictUsingLocal($model)` to force the local model to WordPress.
- `resolveConflictUsingRemote($model)` to replace the local model with the remote payload.
- `resolveConflictManually($model, $payload)` after building an explicit merged payload.

## Media Lifecycle

`media()->records()->pull()` stores attachment records and source URLs. `media()->files()->download($media)`
copies remote bytes into Laravel Storage. `media()->files()->upload($data)` sends file bytes to
the WordPress Media Library and persists the returned attachment record locally.
`media()->files()->deleteLocal($media)` deletes only the local copied file. Physical files are
never deleted as a side effect of record sync.

See [media files](docs/02-user-guide/media-files.md).

## Adding a New Resource

Add a `ResourceDefinition`, register it in `ResourceRegistry`, expose it from the appropriate
grouped service if needed, and cover payload mapping plus sync behavior with focused tests. Keep
resource definitions as adapters and keep database or remote coordination in services. See
[adding a resource](docs/04-development/adding-resource.md).

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

## Real Docker Integration Workflow

The Docker workflow installs a fresh Laravel app, installs this package into it with a Composer
path repository, installs WordPress with WP-CLI, seeds real WordPress records and media files,
runs Laravel package migrations, and executes a PHPUnit smoke test against the real services.

Prerequisites: Docker and Docker Compose.

```bash
./scripts/test-docker.sh
```

The workflow validates package discovery, config loading, package migrations, WP-CLI
availability, WordPress installation, real WordPress post/media generation, WordPress-to-Laravel
pull sync, media record pull, explicit media file download, update handling, idempotent repeated
pull behavior, Laravel-originated post create/update push through DTOs, Laravel-originated page
create push through DTOs, taxonomy assignment, featured media assignment, unpublish, trash
behavior with local reflection, and dirty-local conflict detection against a real remote change.
It writes:

```text
artifacts/integration-report.json
artifacts/integration-summary.txt
artifacts/junit.xml
```

The JSON report includes environment versions, executed paths/commands, package capabilities,
schema audit notes, WordPress record counts, Laravel record counts, media record/file evidence,
pull/push/idempotency results, assertions, skipped assertions, failures, and limitations. Media is
reported in three separate buckets: WordPress attachments, Laravel media records, and local copied
files. Useful defaults can be overridden with environment variables such as `DB_HOST`,
`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `WORDPRESS_PATH`, `WORDPRESS_URL`,
`LARAVEL_APP_PATH`, and `PACKAGE_PATH`.

See [Docker WordPress testing](docs/04-development/docker-wordpress.md).

## Known Limitations

- Media upload uses the SDK's Media Library upload endpoint; field acceptance still depends on
  WordPress runtime permissions and REST support.
- Posts/pages have feature-level coverage; many generic resource definitions are available
  through shared services but are not all feature-complete.
- Author assignment depends on runtime WordPress REST permissions and configuration.
- Custom meta requires WordPress meta keys registered with `show_in_rest=true`.
- Endpoint shape and capabilities can vary by WordPress version, plugins, themes, auth type, and
  user permissions.
