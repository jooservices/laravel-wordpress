<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Page;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PageResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('pages', EntityType::Page, 'pages', Page::class, 'pages', true, true, true);
    }
}
