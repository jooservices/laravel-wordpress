<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Content;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class ResourceContentService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function resource(): ResourceService
    {
        return $this->resource('posts');
    }
}
