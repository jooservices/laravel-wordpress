<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Users;

final readonly class RemoteUserData
{
    public function __construct(
        public int $id,
        public array $payload,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self((int) ($payload['id'] ?? 0), $payload);
    }

    public static function fromSdk(object|array $user): self
    {
        $payload = is_array($user) ? $user : json_decode(json_encode($user, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);

        return self::fromArray($payload);
    }

    public function toLocalPayload(): array
    {
        return [
            'remote_id' => $this->id,
            'username' => $this->payload['username'] ?? null,
            'name' => $this->payload['name'] ?? null,
            'first_name' => $this->payload['first_name'] ?? null,
            'last_name' => $this->payload['last_name'] ?? null,
            'email' => $this->payload['email'] ?? null,
            'url' => $this->payload['url'] ?? null,
            'description' => $this->payload['description'] ?? null,
            'link' => $this->payload['link'] ?? null,
            'locale' => $this->payload['locale'] ?? null,
            'nickname' => $this->payload['nickname'] ?? null,
            'slug' => $this->payload['slug'] ?? null,
            'registered_at' => $this->payload['registered_date'] ?? null,
            'roles' => $this->payload['roles'] ?? null,
            'capabilities' => $this->payload['capabilities'] ?? null,
            'extra_capabilities' => $this->payload['extra_capabilities'] ?? null,
            'avatar_urls' => $this->payload['avatar_urls'] ?? null,
            'meta' => $this->payload['meta'] ?? null,
            'raw_payload' => $this->payload,
        ];
    }

    public function syncPayload(): array
    {
        $payload = $this->toLocalPayload();
        unset($payload['raw_payload']);

        return $payload;
    }
}
