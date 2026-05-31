<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Widgets;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Sidebar;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class SidebarResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('sidebars', EntityType::Sidebar, 'sidebars', Sidebar::class, 'sidebars', false, false, false);
    }
}
