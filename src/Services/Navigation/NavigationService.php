<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Navigation;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class NavigationService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function navigations(): ResourceService
    {
        return $this->resource('navigations');
    }

    public function menus(): ResourceService
    {
        return $this->resource('menus');
    }

    public function menuItems(): ResourceService
    {
        return $this->resource('menu-items');
    }

    public function locations(): ResourceService
    {
        return $this->resource('menu-locations');
    }
}
