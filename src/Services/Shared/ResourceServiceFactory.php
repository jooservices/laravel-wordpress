<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Jooservices\LaravelWordPress\Models\Site;

final readonly class ResourceServiceFactory
{
    public function __construct(
        private ResourceRegistry $registry,
        private ResourceLocalService $local,
        private ResourceRemoteService $remote,
        private ResourceSyncService $sync,
        private SyncStateChecker $checker,
    ) {}

    public function make(Site $site, string $key): ResourceService
    {
        return new ResourceService(
            $site,
            $this->registry->get($key),
            $this->local,
            $this->remote,
            $this->sync,
            $this->checker,
        );
    }
}
