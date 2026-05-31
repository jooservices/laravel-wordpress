<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Shared;

final readonly class ListQuery
{
    public function __construct(
        public string $context = 'edit',
        public int $page = 1,
        public int $perPage = 100,
        public ?string $search = null,
        public ?string $status = null,
    ) {}

    public function toPayload(): array
    {
        return array_filter([
            'context' => $this->context,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'search' => $this->search,
            'status' => $this->status,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
