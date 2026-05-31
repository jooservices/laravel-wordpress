<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Content;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Page;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;
use Jooservices\LaravelWordPress\Resources\Content\Concerns\MapsContentPayload;

final class PageResource extends BaseResourceDefinition
{
    use MapsContentPayload;

    public function __construct()
    {
        parent::__construct('pages', EntityType::Page, 'pages', Page::class, 'pages', true, true, true);
    }

    public function toLocalPayload(array|object $remote): array
    {
        return $this->contentLocalPayload($remote, includeTaxonomies: false);
    }

    public function toRemotePayload(Model $model): array
    {
        return $this->contentRemotePayload($model, includeTaxonomies: false);
    }
}
