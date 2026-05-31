<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\PostRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PostRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('post-revisions', EntityType::PostRevision, 'post_revisions', PostRevision::class, 'revisions', true, true, true);
    }
}
