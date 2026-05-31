<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Template;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class TemplateResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('templates', EntityType::Template, 'templates', Template::class, 'templates', true, true, true);
    }
}
