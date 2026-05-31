<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Users;

use BadMethodCallException;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class UserService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function users(): ResourceService
    {
        return $this->resource('users');
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
