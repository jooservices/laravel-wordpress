<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Shared;

final readonly class SyncConflict
{
    public function __construct(
        public string $entityType,
        public int|string|null $localId,
        public int|string|null $remoteId,
        public string $message,
        public ?array $remotePayload = null,
    ) {}
}
