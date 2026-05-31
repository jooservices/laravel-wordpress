<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Post;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PostResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('posts', EntityType::Post, 'posts', Post::class, 'posts', true, true, true);
    }
}
