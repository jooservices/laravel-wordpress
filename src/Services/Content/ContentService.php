<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Content;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class ContentService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function posts(): ResourceService
    {
        return $this->resource('posts');
    }

    public function pages(): ResourceService
    {
        return $this->resource('pages');
    }

    public function revisions(string $type = 'post'): ResourceService
    {
        return $this->resource($type === 'page' ? 'page-revisions' : 'post-revisions');
    }

    public function autosaves(string $type = 'post'): ResourceService
    {
        return $this->resource($type === 'page' ? 'page-autosaves' : 'post-autosaves');
    }

    public function type(string $type): ResourceService
    {
        return $this->resource('posts');
    }
}
