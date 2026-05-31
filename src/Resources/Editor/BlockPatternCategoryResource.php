<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\BlockPatternCategory;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class BlockPatternCategoryResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('block-pattern-categories', EntityType::BlockPatternCategory, 'block_pattern_categories', BlockPatternCategory::class, 'custom', false, false, false);
    }
}
