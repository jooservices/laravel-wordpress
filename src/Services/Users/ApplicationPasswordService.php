<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Users;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class ApplicationPasswordService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function applicationPasswords(): ResourceService
    {
        return $this->resources->make($this->site, 'application-passwords');
    }
}
