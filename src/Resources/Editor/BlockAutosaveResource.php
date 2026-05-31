<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\BlockAutosave;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class BlockAutosaveResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('block-autosaves', EntityType::BlockAutosave, 'block_autosaves', BlockAutosave::class, 'revisions', true, true, true);
    }
}
