<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\BlockRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class BlockRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('block-revisions', EntityType::BlockRevision, 'block_revisions', BlockRevision::class, 'revisions', true, true, true);
    }
}
