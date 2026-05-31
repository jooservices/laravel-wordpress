<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\BlockPattern;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class BlockPatternResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('block-patterns', EntityType::BlockPattern, 'block_patterns', BlockPattern::class, 'custom', false, false, false);
    }
}
