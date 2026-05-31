<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Widgets;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class WidgetService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function sidebars(): ResourceService
    {
        return $this->resources->make($this->site, 'sidebars');
    }

    public function widgets(): ResourceService
    {
        return $this->resources->make($this->site, 'widgets');
    }

    public function types(): ResourceService
    {
        return $this->resources->make($this->site, 'widget-types');
    }
}
