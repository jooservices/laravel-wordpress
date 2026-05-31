<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Taxonomy;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Taxonomy;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class TaxonomyResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('taxonomies', EntityType::Taxonomy, 'taxonomies', Taxonomy::class, 'taxonomies', false, false, false);
    }
}
