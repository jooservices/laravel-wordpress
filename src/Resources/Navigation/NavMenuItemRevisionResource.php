<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Navigation;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\NavMenuItemRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class NavMenuItemRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('menu-item-revisions', EntityType::NavMenuItemRevision, 'nav_menu_item_revisions', NavMenuItemRevision::class, 'revisions', true, true, true);
    }
}
