<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;

final class ResourceLocalService
{
    public function list(ResourceDefinition $definition, Site $site): Collection
    {
        return $definition->modelClass()::query()->where('site_id', $site->id)->get();
    }

    public function get(ResourceDefinition $definition, int|string $id): ?Model
    {
        return $definition->modelClass()::query()->find($id);
    }

    public function create(ResourceDefinition $definition, Site $site, array $payload): Model
    {
        return $definition->modelClass()::query()->create(['site_id' => $site->id] + $payload);
    }

    public function update(Model $model, array $payload): Model
    {
        $model->fill($payload);
        $model->save();

        return $model;
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    public function findRemote(ResourceDefinition $definition, Site $site, int|string $remoteId): ?Model
    {
        return $definition->modelClass()::query()
            ->where('site_id', $site->id)
            ->where('remote_id', $remoteId)
            ->first();
    }
}
