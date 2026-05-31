<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Editor;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\GlobalStyle;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class GlobalStyleResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('global-styles', EntityType::GlobalStyle, 'global_styles', GlobalStyle::class, 'globalStyles', true, true, true);
    }
}
