<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\System;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Setting;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class SettingResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('settings', EntityType::Setting, 'settings', Setting::class, 'settings', false, true, false);
    }
}
