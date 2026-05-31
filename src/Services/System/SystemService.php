<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\System;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class SystemService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function settings(): ResourceService
    {
        return $this->resources->make($this->site, 'settings');
    }

    public function options(): ResourceService
    {
        return $this->resources->make($this->site, 'options');
    }

    public function postTypes(): ResourceService
    {
        return $this->resources->make($this->site, 'post-types');
    }

    public function postStatuses(): ResourceService
    {
        return $this->resources->make($this->site, 'post-statuses');
    }

    public function themes(): ResourceService
    {
        return $this->resources->make($this->site, 'themes');
    }

    public function plugins(): ResourceService
    {
        return $this->resources->make($this->site, 'plugins');
    }
}
