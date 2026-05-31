<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Widgets;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class WidgetService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function sidebars(): ResourceService
    {
        return $this->resource('sidebars');
    }

    public function widgets(): ResourceService
    {
        return $this->resource('widgets');
    }

    public function types(): ResourceService
    {
        return $this->resource('widget-types');
    }
}
