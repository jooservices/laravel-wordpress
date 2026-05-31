<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Users;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\User;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class UserResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('users', EntityType::User, 'users', User::class, 'users');
    }

    public function toLocalPayload(array|object $remote): array
    {
        $payload = $this->payloadArray($remote);

        return [
            'remote_id' => isset($payload['id']) ? (int) $payload['id'] : null,
            'username' => $payload['username'] ?? null,
            'name' => $payload['name'] ?? null,
            'first_name' => $payload['first_name'] ?? null,
            'last_name' => $payload['last_name'] ?? null,
            'email' => $payload['email'] ?? null,
            'url' => $payload['url'] ?? null,
            'description' => $payload['description'] ?? null,
            'link' => $payload['link'] ?? null,
            'locale' => $payload['locale'] ?? null,
            'nickname' => $payload['nickname'] ?? null,
            'slug' => $payload['slug'] ?? null,
            'registered_at' => $payload['registered_date'] ?? $payload['registered_at'] ?? null,
            'roles' => $payload['roles'] ?? null,
            'capabilities' => $payload['capabilities'] ?? null,
            'extra_capabilities' => $payload['extra_capabilities'] ?? null,
            'avatar_urls' => $payload['avatar_urls'] ?? null,
            'meta' => $payload['meta'] ?? null,
            'raw_payload' => $payload,
        ];
    }

    public function syncPayload(Model|array $payload): array
    {
        $data = parent::syncPayload($payload);
        unset($data['registered_at'], $data['capabilities'], $data['extra_capabilities'], $data['avatar_urls'], $data['link']);

        return $data;
    }
}
