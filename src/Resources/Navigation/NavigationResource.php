<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Navigation;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Navigation;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class NavigationResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('navigations', EntityType::Navigation, 'navigations', Navigation::class, 'navigations', true, true, true);
    }
}
