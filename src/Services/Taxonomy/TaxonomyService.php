<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Taxonomy;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class TaxonomyService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function taxonomies(): ResourceService
    {
        return $this->resource('taxonomies');
    }

    public function categories(): ResourceService
    {
        return $this->resource('terms');
    }

    public function tags(): ResourceService
    {
        return $this->resource('terms');
    }

    public function terms(string $taxonomy): ResourceService
    {
        return $this->resource('terms');
    }
}
