<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\TemplatePart;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class TemplatePartResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('template-parts', EntityType::TemplatePart, 'template_parts', TemplatePart::class, 'templateParts', true, true, true);
    }
}
