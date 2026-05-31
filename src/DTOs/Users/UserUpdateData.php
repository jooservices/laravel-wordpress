<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Users;

final readonly class UserUpdateData
{
    public function __construct(
        public ?string $username = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $url = null,
        public ?string $description = null,
        public ?string $locale = null,
        public ?string $nickname = null,
        public ?string $slug = null,
        public ?array $roles = null,
        public ?array $meta = null,
    ) {}

    public function toRemotePayload(): array
    {
        return array_filter([
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'name' => $this->name,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'url' => $this->url,
            'description' => $this->description,
            'locale' => $this->locale,
            'nickname' => $this->nickname,
            'slug' => $this->slug,
            'roles' => $this->roles,
            'meta' => $this->meta,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function toLocalPayload(): array
    {
        $payload = $this->toRemotePayload();
        unset($payload['password']);

        return $payload;
    }
}
