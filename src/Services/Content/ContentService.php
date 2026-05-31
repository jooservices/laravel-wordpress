<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Content;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\DTOs\Content\PageCreateData;
use Jooservices\LaravelWordPress\DTOs\Content\PageUpdateData;
use Jooservices\LaravelWordPress\DTOs\Content\PostCreateData;
use Jooservices\LaravelWordPress\DTOs\Content\PostUpdateData;
use Jooservices\LaravelWordPress\DTOs\Shared\SyncConflict;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Models\Page;
use Jooservices\LaravelWordPress\Models\Post;
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

    public function createPost(PostCreateData $data, bool $push = false): Model|SyncConflict
    {
        $post = $this->posts()->createLocal($data->toLocalPayload());

        return $push ? $this->posts()->push($post) : $post;
    }

    public function updatePost(Post $post, PostUpdateData $data, bool $push = false, bool $force = false): Model|SyncConflict
    {
        $post = $this->posts()->updateLocal($post, $data->toLocalPayload());

        return $push ? $this->posts()->push($post, $force) : $post;
    }

    public function pushPost(Post $post, bool $force = false): Model|SyncConflict
    {
        return $this->posts()->push($post, $force);
    }

    public function createPage(PageCreateData $data, bool $push = false): Model|SyncConflict
    {
        $page = $this->pages()->createLocal($data->toLocalPayload());

        return $push ? $this->pages()->push($page) : $page;
    }

    public function updatePage(Page $page, PageUpdateData $data, bool $push = false, bool $force = false): Model|SyncConflict
    {
        $page = $this->pages()->updateLocal($page, $data->toLocalPayload());

        return $push ? $this->pages()->push($page, $force) : $page;
    }

    public function pushPage(Page $page, bool $force = false): Model|SyncConflict
    {
        return $this->pages()->push($page, $force);
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
