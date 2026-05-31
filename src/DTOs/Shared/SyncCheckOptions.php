<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Shared;

final readonly class SyncCheckOptions
{
    public function __construct(
        public bool $apply = false,
        public bool $includeRemotePayload = true,
    ) {}
}
