<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Taxonomy;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\Term;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class TermResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('terms', EntityType::Term, 'terms', Term::class, 'categories', true, true, true);
    }
}
