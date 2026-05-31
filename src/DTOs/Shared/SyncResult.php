<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Shared;

final readonly class SyncResult
{
    public function __construct(
        public int $processed,
        public int $succeeded,
        public int $failed,
        public int $conflicted = 0,
        public array $errors = [],
        public array $conflicts = [],
    ) {}

    public static function empty(): self
    {
        return new self(0, 0, 0);
    }

    public function toArray(): array
    {
        return [
            'processed' => $this->processed,
            'succeeded' => $this->succeeded,
            'failed' => $this->failed,
            'conflicted' => $this->conflicted,
            'errors' => array_map(static fn ($error) => method_exists($error, 'toArray') ? $error->toArray() : $error, $this->errors),
            'conflicts' => $this->conflicts,
        ];
    }
}
