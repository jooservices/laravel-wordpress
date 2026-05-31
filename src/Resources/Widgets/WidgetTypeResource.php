<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Widgets;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\WidgetType;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class WidgetTypeResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('widget-types', EntityType::WidgetType, 'widget_types', WidgetType::class, 'widgetTypes', false, false, false);
    }
}
