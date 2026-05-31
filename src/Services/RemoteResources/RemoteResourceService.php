<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\RemoteResources;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceLocalService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceRegistry;
use Jooservices\LaravelWordPress\Services\Shared\ResourceRemoteService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceSyncService;
use Jooservices\LaravelWordPress\Services\Shared\SyncStateChecker;

final class RemoteResourceService
{
    public function __construct(private readonly Site $site) {}

    public function endpoint(string $endpoint): ResourceService
    {
        return new ResourceService(
            $this->site,
            app(ResourceRegistry::class)->remote($endpoint),
            app(ResourceLocalService::class),
            app(ResourceRemoteService::class),
            app(ResourceSyncService::class),
            app(SyncStateChecker::class),
        );
    }
}
