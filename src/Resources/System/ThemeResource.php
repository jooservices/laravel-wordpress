<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\System;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Theme;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class ThemeResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('themes', EntityType::Theme, 'themes', Theme::class, 'themes', false, false, false);
    }
}
