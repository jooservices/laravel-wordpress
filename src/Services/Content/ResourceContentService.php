<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Content;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class ResourceContentService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function resource(): ResourceService
    {
        return $this->resources->make($this->site, 'posts');
    }
}
