<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Block;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class BlockResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('blocks', EntityType::Block, 'blocks', Block::class, 'blocks', true, true, true);
    }
}
