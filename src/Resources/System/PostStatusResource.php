<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\System;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\PostStatus;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PostStatusResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('post-statuses', EntityType::PostStatus, 'post_statuses', PostStatus::class, 'statuses', false, false, false);
    }
}
