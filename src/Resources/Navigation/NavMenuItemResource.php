<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Navigation;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\NavMenuItem;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class NavMenuItemResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('menu-items', EntityType::NavMenuItem, 'nav_menu_items', NavMenuItem::class, 'navMenuItems', true, true, true);
    }
}
