<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\System;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Plugin;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class PluginResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('plugins', EntityType::Plugin, 'plugins', Plugin::class, 'plugins', false, false, false);
    }
}
