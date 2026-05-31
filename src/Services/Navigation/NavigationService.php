<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Navigation;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class NavigationService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function navigations(): ResourceService
    {
        return $this->resources->make($this->site, 'navigations');
    }

    public function menus(): ResourceService
    {
        return $this->resources->make($this->site, 'menus');
    }

    public function menuItems(): ResourceService
    {
        return $this->resources->make($this->site, 'menu-items');
    }

    public function locations(): ResourceService
    {
        return $this->resources->make($this->site, 'menu-locations');
    }
}
