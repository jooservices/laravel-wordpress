<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\System;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\PostType;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PostTypeResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('post-types', EntityType::PostType, 'post_types', PostType::class, 'postTypes', false, false, false);
    }
}
