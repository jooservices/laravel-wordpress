<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\PostAutosave;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PostAutosaveResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('post-autosaves', EntityType::PostAutosave, 'post_autosaves', PostAutosave::class, 'revisions', true, true, true);
    }
}
