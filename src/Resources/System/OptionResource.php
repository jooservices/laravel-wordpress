<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\System;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Option;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class OptionResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('options', EntityType::Option, 'options', Option::class, 'custom', true, true, true);
    }
}
