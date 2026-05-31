<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Shared;

final readonly class SyncError
{
    public function __construct(
        public string $entityType,
        public int|string|null $localId,
        public int|string|null $remoteId,
        public string $message,
    ) {}

    public function toArray(): array
    {
        return [
            'entity_type' => $this->entityType,
            'local_id' => $this->localId,
            'remote_id' => $this->remoteId,
            'message' => $this->message,
        ];
    }
}
