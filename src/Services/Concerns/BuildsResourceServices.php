<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Concerns;

use Jooservices\LaravelWordPress\Services\Shared\ResourceLocalService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceRegistry;
use Jooservices\LaravelWordPress\Services\Shared\ResourceRemoteService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceSyncService;
use Jooservices\LaravelWordPress\Services\Shared\SyncStateChecker;

trait BuildsResourceServices
{
    protected function resource(string $key): ResourceService
    {
        return new ResourceService(
            $this->site,
            app(ResourceRegistry::class)->get($key),
            app(ResourceLocalService::class),
            app(ResourceRemoteService::class),
            app(ResourceSyncService::class),
            app(SyncStateChecker::class),
        );
    }
}
