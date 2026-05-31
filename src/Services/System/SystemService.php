<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\System;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class SystemService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function settings(): ResourceService
    {
        return $this->resource('settings');
    }

    public function options(): ResourceService
    {
        return $this->resource('options');
    }

    public function postTypes(): ResourceService
    {
        return $this->resource('post-types');
    }

    public function postStatuses(): ResourceService
    {
        return $this->resource('post-statuses');
    }

    public function themes(): ResourceService
    {
        return $this->resource('themes');
    }

    public function plugins(): ResourceService
    {
        return $this->resource('plugins');
    }
}
