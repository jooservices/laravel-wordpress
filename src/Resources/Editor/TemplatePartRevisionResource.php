<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\TemplatePartRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class TemplatePartRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('template-part-revisions', EntityType::TemplatePartRevision, 'template_part_revisions', TemplatePartRevision::class, 'revisions', true, true, true);
    }
}
