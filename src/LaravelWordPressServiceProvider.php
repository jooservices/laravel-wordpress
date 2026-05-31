<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress;

use Illuminate\Support\ServiceProvider;
use Jooservices\LaravelWordPress\Services\Manager;
use Jooservices\LaravelWordPress\Services\Shared\MediaStorage;
use Jooservices\LaravelWordPress\Services\Shared\PayloadHasher;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;
use Jooservices\LaravelWordPress\Services\Shared\SyncStateChecker;

final class LaravelWordPressServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/wordpress.php', 'wordpress');

        $this->app->singleton(Manager::class);
        $this->app->alias(Manager::class, 'laravel-wordpress');

        $this->app->singleton(PayloadHasher::class);
        $this->app->singleton(SyncStateChecker::class);
        $this->app->singleton(MediaStorage::class);
        $this->app->singleton(ResourceServiceFactory::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/wordpress.php' => config_path('wordpress.php'),
        ], 'laravel-wordpress-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }
}
