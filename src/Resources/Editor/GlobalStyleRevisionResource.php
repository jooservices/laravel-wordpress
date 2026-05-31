<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\GlobalStyleRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class GlobalStyleRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('global-style-revisions', EntityType::GlobalStyleRevision, 'global_style_revisions', GlobalStyleRevision::class, 'revisions', true, true, true);
    }
}
