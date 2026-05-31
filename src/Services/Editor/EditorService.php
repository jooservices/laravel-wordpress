<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Editor;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class EditorService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function blocks(): ResourceService
    {
        return $this->resource('blocks');
    }

    public function blockTypes(): ResourceService
    {
        return $this->resource('block-types');
    }

    public function patterns(): ResourceService
    {
        return $this->resource('block-patterns');
    }

    public function templates(): ResourceService
    {
        return $this->resource('templates');
    }

    public function templateParts(): ResourceService
    {
        return $this->resource('template-parts');
    }

    public function globalStyles(): ResourceService
    {
        return $this->resource('global-styles');
    }
}
