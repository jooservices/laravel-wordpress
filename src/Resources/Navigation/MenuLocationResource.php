<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Navigation;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\MenuLocation;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class MenuLocationResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('menu-locations', EntityType::MenuLocation, 'menu_locations', MenuLocation::class, 'menuLocations', false, false, false);
    }
}
