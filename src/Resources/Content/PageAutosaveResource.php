<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\PageAutosave;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PageAutosaveResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('page-autosaves', EntityType::PageAutosave, 'page_autosaves', PageAutosave::class, 'revisions', true, true, true);
    }
}
