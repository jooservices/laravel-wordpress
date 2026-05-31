<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Content;

use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceRegistry;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class ContentService
{
    use BuildsResourceServices;

    public function __construct(
        private readonly Site $site,
        private readonly ResourceRegistry $resources,
    ) {}

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
        $key = match ($type) {
            'post' => 'posts',
            'page' => 'pages',
            default => $type,
        };

        try {
            $definition = $this->resources->get($key);
        } catch (\InvalidArgumentException) {
            throw new WordPressException("Content type [{$type}] is not supported by this package.");
        }

        return $this->resource($definition->key());
    }
}
