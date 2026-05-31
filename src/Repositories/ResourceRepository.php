<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Repositories;

use Jooservices\LaravelWordPress\Models\RemoteResource;

final class ResourceRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new RemoteResource);
    }
}
