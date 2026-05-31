<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Comments;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Comment;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class CommentResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('comments', EntityType::Comment, 'comments', Comment::class, 'comments', true, true, true);
    }
}
