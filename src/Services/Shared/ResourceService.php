<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\DTOs\Shared\SyncResult;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;

final class ResourceService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceDefinition $definition,
        private readonly ResourceLocalService $local,
        private readonly ResourceRemoteService $remote,
        private readonly ResourceSyncService $sync,
        private readonly SyncStateChecker $checker,
    ) {}

    public function listRemote(array $query = []): array
    {
        return $this->remote->list($this->definition, $this->site, $query);
    }

    public function getRemote(int|string $remoteId): mixed
    {
        return $this->remote->get($this->definition, $this->site, $remoteId);
    }

    public function createRemote(array $payload): mixed
    {
        return $this->remote->create($this->definition, $this->site, $payload);
    }

    public function updateRemote(int|string $remoteId, array $payload): mixed
    {
        return $this->remote->update($this->definition, $this->site, $remoteId, $payload);
    }

    public function deleteRemote(int|string $remoteId, bool $force = false): mixed
    {
        return $this->remote->delete($this->definition, $this->site, $remoteId, $force);
    }

    public function listLocal(): Collection
    {
        return $this->local->list($this->definition, $this->site);
    }

    public function getLocal(int|string $id): ?Model
    {
        return $this->local->get($this->definition, $id);
    }

    public function createLocal(array $payload): Model
    {
        return $this->local->create($this->definition, $this->site, $payload);
    }

    public function updateLocal(Model $model, array $payload): Model
    {
        return $this->local->update($model, $payload);
    }

    public function deleteLocal(Model $model): bool
    {
        return $this->local->delete($model);
    }

    public function pull(array $query = []): SyncResult
    {
        return $this->sync->pull($this->definition, $this->site, $query);
    }

    public function pullOne(int|string $remoteId): object
    {
        return $this->sync->pullOne($this->definition, $this->site, $remoteId);
    }

    public function push(Model $model, bool $force = false): object
    {
        return $this->sync->push($this->definition, $this->site, $model, $force);
    }

    public function sync(array $query = []): SyncResult
    {
        return $this->pull($query);
    }

    public function checkSyncState(Model $model): object
    {
        return $this->checker->check($this->definition, $model);
    }

    public function checkLocalChanged(Model $model): bool
    {
        return $this->checkSyncState($model)->localChanged;
    }

    public function checkRemoteChanged(Model $model): bool
    {
        return $this->checkSyncState($model)->remoteChanged;
    }

    public function resolveConflictUsingLocal(Model $model): object
    {
        return $this->push($model, true);
    }

    public function resolveConflictUsingRemote(Model $model): object
    {
        return $this->pullOne($model->remote_id);
    }

    public function resolveConflictManually(Model $model, array $payload): Model
    {
        return $this->local->update($model, $payload + ['conflict_payload' => null, 'conflicted_at' => null]);
    }
}
