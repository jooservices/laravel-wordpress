<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Navigation;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\NavMenu;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class NavMenuResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('menus', EntityType::NavMenu, 'nav_menus', NavMenu::class, 'navMenus', true, true, true);
    }
}
