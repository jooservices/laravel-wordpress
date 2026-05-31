<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Shared;

use Jooservices\LaravelWordPress\Enums\SyncDecision;
use Jooservices\LaravelWordPress\Enums\SyncStatus;

final readonly class SyncStateCheckResult
{
    public function __construct(
        public int|string|null $localId,
        public int|string|null $remoteId,
        public string $entityType,
        public bool $existsLocal,
        public bool $existsRemote,
        public bool $localChanged,
        public bool $remoteChanged,
        public bool $hasConflict,
        public SyncStatus $currentStatus,
        public SyncDecision $recommendedAction,
        public ?string $message = null,
        public ?array $remotePayload = null,
        public ?array $localPayload = null,
    ) {}
}
