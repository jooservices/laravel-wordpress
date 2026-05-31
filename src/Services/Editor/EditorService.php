<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Editor;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class EditorService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function blocks(): ResourceService
    {
        return $this->resources->make($this->site, 'blocks');
    }

    public function blockTypes(): ResourceService
    {
        return $this->resources->make($this->site, 'block-types');
    }

    public function patterns(): ResourceService
    {
        return $this->resources->make($this->site, 'block-patterns');
    }

    public function templates(): ResourceService
    {
        return $this->resources->make($this->site, 'templates');
    }

    public function templateParts(): ResourceService
    {
        return $this->resources->make($this->site, 'template-parts');
    }

    public function globalStyles(): ResourceService
    {
        return $this->resources->make($this->site, 'global-styles');
    }
}
