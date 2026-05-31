<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\PageRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PageRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('page-revisions', EntityType::PageRevision, 'page_revisions', PageRevision::class, 'revisions', true, true, true);
    }
}
