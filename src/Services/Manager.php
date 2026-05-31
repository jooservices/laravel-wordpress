<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services;

use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Sites\CredentialService;
use Jooservices\LaravelWordPress\Services\Sites\SiteService;

final class Manager
{
    public function sites(): SiteService
    {
        return app(SiteService::class);
    }

    public function credentials(): CredentialService
    {
        return app(CredentialService::class);
    }

    public function site(Site|int|string $site): SiteContext
    {
        $model = $site instanceof Site ? $site : Site::query()->findOrFail($site);

        return new SiteContext($model);
    }
}
