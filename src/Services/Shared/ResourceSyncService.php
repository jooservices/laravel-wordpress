<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Jooservices\LaravelWordPress\DTOs\Shared\SyncConflict;
use Jooservices\LaravelWordPress\DTOs\Shared\SyncResult;
use Jooservices\LaravelWordPress\Enums\SyncStatus;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;

final class ResourceSyncService
{
    public function __construct(
        private readonly ResourceLocalService $local,
        private readonly ResourceRemoteService $remote,
        private readonly PayloadHasher $hasher,
    ) {}

    public function pull(ResourceDefinition $definition, Site $site, array $query = []): SyncResult
    {
        $processed = $succeeded = $conflicted = 0;
        $conflicts = [];

        foreach ($this->remote->list($definition, $site, $query) as $remotePayload) {
            $processed++;
            $result = $this->pullOnePayload($definition, $site, $remotePayload);
            if ($result instanceof SyncConflict) {
                $conflicted++;
                $conflicts[] = $result;
            } else {
                $succeeded++;
            }
        }

        return new SyncResult($processed, $succeeded, 0, $conflicted, [], $conflicts);
    }

    public function pullOne(ResourceDefinition $definition, Site $site, int|string $remoteId): Model|SyncConflict
    {
        return $this->pullOnePayload($definition, $site, $this->remote->get($definition, $site, $remoteId));
    }

    public function push(ResourceDefinition $definition, Site $site, Model $model, bool $force = false): Model|SyncConflict
    {
        if (! $force && $model->remote_id !== null && $model->sync_status === SyncStatus::Dirty) {
            $remote = $this->remote->get($definition, $site, $model->remote_id);
            $remoteHash = $this->hasher->hash($definition->syncPayload($definition->toLocalPayload($remote)));
            if ($model->remote_hash !== null && $model->remote_hash !== $remoteHash) {
                return $this->markConflict($model, $definition->toLocalPayload($remote));
            }
        }

        $response = $model->remote_id === null
            ? $this->remote->create($definition, $site, $definition->toRemotePayload($model))
            : $this->remote->update($definition, $site, $model->remote_id, $definition->toRemotePayload($model));

        $payload = $definition->toLocalPayload($response);

        return $this->applySyncedPayload($definition, $model, $payload, pushed: true);
    }

    private function pullOnePayload(ResourceDefinition $definition, Site $site, mixed $remotePayload): Model|SyncConflict
    {
        $payload = $definition->toLocalPayload($remotePayload);
        $remoteId = $payload['remote_id'] ?? null;
        $model = $remoteId !== null ? $this->local->findRemote($definition, $site, $remoteId) : null;

        if ($model instanceof Model && $model->sync_status === SyncStatus::Dirty) {
            return $this->markConflict($model, $payload);
        }

        $model ??= $this->local->create($definition, $site, []);

        return $this->applySyncedPayload($definition, $model, $payload, pulled: true);
    }

    private function applySyncedPayload(ResourceDefinition $definition, Model $model, array $payload, bool $pulled = false, bool $pushed = false): Model
    {
        $syncPayload = $definition->syncPayload($payload);
        $now = Carbon::now();

        $payload['sync_status'] = SyncStatus::Synced;
        $payload['remote_hash'] = $this->hasher->hash($syncPayload);
        $payload['local_hash'] = $payload['remote_hash'];
        $payload['synced_at'] = $now;
        $payload['conflict_payload'] = null;
        $payload['conflicted_at'] = null;
        if ($pulled) {
            $payload['last_pulled_at'] = $now;
        }
        if ($pushed) {
            $payload['last_pushed_at'] = $now;
        }

        $model->fill($payload);
        $model->save();

        return $model;
    }

    private function markConflict(Model $model, array $remotePayload): SyncConflict
    {
        $model->fill([
            'sync_status' => SyncStatus::Conflict,
            'conflict_payload' => $remotePayload,
            'conflicted_at' => Carbon::now(),
        ]);
        $model->save();

        return new SyncConflict($model::class, (string) $model->getKey(), $remotePayload);
    }
}
