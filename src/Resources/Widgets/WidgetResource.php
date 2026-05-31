<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Widgets;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Widget;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class WidgetResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('widgets', EntityType::Widget, 'widgets', Widget::class, 'widgets', true, true, true);
    }
}
