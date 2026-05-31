<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Taxonomy;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class TaxonomyService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function taxonomies(): ResourceService
    {
        return $this->resources->make($this->site, 'taxonomies');
    }

    public function categories(): ResourceService
    {
        return $this->resources->make($this->site, 'terms');
    }

    public function tags(): ResourceService
    {
        return $this->resources->make($this->site, 'terms');
    }

    public function terms(string $taxonomy): ResourceService
    {
        return $this->resources->make($this->site, 'terms');
    }
}
