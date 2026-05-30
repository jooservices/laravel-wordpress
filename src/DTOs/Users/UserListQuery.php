<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Users;

final readonly class UserListQuery
{
    public function __construct(
        public string $context = 'edit',
        public int $page = 1,
        public int $perPage = 100,
        public ?string $search = null,
        public ?array $roles = null,
    ) {}

    public function toPayload(): array
    {
        return array_filter([
            'context' => $this->context,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'search' => $this->search,
            'roles' => $this->roles,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
