<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    public function __construct(
        private readonly Model $model,
    ) {}

    public function model(): Model
    {
        return $this->model;
    }

    public function find(int|string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    public function create(array $payload): Model
    {
        return $this->model->newQuery()->create($payload);
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

    public function forSite(int $siteId): Collection
    {
        return $this->model->newQuery()->where('site_id', $siteId)->get();
    }

    public function findRemote(int $siteId, int|string $remoteId): ?Model
    {
        return $this->model->newQuery()
            ->where('site_id', $siteId)
            ->where('remote_id', $remoteId)
            ->first();
    }
}
