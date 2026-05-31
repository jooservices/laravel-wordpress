<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\BlockType;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class BlockTypeResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('block-types', EntityType::BlockType, 'block_types', BlockType::class, 'blockTypes', false, false, false);
    }
}
