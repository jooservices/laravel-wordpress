<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Users;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Concerns\BuildsResourceServices;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;

final class ApplicationPasswordService
{
    use BuildsResourceServices;

    public function __construct(private readonly Site $site) {}

    public function applicationPasswords(): ResourceService
    {
        return $this->resource('application-passwords');
    }
}
