<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Sites;

use Jooservices\LaravelWordPress\DTOs\Credentials\CredentialCreateData;
use Jooservices\LaravelWordPress\Models\Credential;
use Jooservices\LaravelWordPress\Models\Site;

final class CredentialService
{
    public function createForSite(Site $site, CredentialCreateData|array $data): Credential
    {
        $payload = $data instanceof CredentialCreateData ? $data->toArray() : $data;
        if (($payload['is_default'] ?? true) === true) {
            Credential::query()->where('site_id', $site->id)->update(['is_default' => false]);
        }

        return Credential::query()->create(['site_id' => $site->id] + $payload);
    }

    public function defaultForSite(Site $site): ?Credential
    {
        return Credential::query()->where('site_id', $site->id)->where('is_default', true)->first();
    }
}
