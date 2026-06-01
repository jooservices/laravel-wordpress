<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Contracts;

use Jooservices\LaravelWordPress\Models\Credential;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\RemoteClient;

interface RemoteClientFactory
{
    public function make(Site $site, ?Credential $credential = null): RemoteClient;
}
