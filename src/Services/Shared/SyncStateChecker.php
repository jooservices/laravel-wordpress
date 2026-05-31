<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\DTOs\Shared\SyncStateCheckResult;
use Jooservices\LaravelWordPress\Enums\SyncDecision;
use Jooservices\LaravelWordPress\Enums\SyncStatus;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;

final class SyncStateChecker
{
    public function __construct(
        private readonly PayloadHasher $hasher,
    ) {}

    public function check(ResourceDefinition $definition, Model $model): SyncStateCheckResult
    {
        $localHash = $this->hasher->hash($definition->syncPayload($model));
        $localChanged = $model->local_hash !== null && $model->local_hash !== $localHash;
        $decision = match (true) {
            $model->remote_id === null => SyncDecision::CreateRemote,
            $model->sync_status === SyncStatus::Conflict => SyncDecision::Conflict,
            $localChanged => SyncDecision::Push,
            default => SyncDecision::Noop,
        };

        return new SyncStateCheckResult($localChanged, false, $decision, $localHash, $model->remote_hash);
    }
}
