<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Comments;

use BadMethodCallException;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class CommentService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function comments(): ResourceService
    {
        return $this->resources->make($this->site, 'comments');
    }

    public function __call(string $method, array $parameters): mixed
    {
        $resource = $this->comments();

        if (! method_exists($resource, $method)) {
            throw new BadMethodCallException("Method [{$method}] does not exist.");
        }

        return $resource->{$method}(...$parameters);
    }
}
