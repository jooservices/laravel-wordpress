<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Sites;

use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Models\Site;

final class SiteService
{
    public function create(SiteCreateData|array $data): Site
    {
        $payload = $data instanceof SiteCreateData ? $data->toArray() : $data;

        return Site::query()->create($payload);
    }

    public function find(int|string $id): ?Site
    {
        return Site::query()->find($id);
    }

    public function all(): object
    {
        return Site::query()->get();
    }

    public function delete(Site $site): bool
    {
        return (bool) $site->delete();
    }
}
