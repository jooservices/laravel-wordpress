<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Jooservices\LaravelWordPress\LaravelWordPressServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [LaravelWordPressServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('filesystems.disks.local.root', sys_get_temp_dir().'/laravel-wordpress-tests');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
