<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Navigation;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\NavigationRevision;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class NavigationRevisionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('navigation-revisions', EntityType::NavigationRevision, 'navigation_revisions', NavigationRevision::class, 'revisions', true, true, true);
    }
}
