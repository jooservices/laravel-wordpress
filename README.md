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