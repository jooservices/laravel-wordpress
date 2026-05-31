<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Users;

use BadMethodCallException;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class UserService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function users(): ResourceService
    {
        return $this->resources->make($this->site, 'users');
    }

    public function __call(string $method, array $parameters): mixed
    {
        $resource = $this->users();

        if (! method_exists($resource, $method)) {
            throw new BadMethodCallException("Method [{$method}] does not exist.");
        }

        return $resource->{$method}(...$parameters);
    }
}
