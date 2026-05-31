<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Credentials;

use Jooservices\LaravelWordPress\Enums\AuthType;

final readonly class CredentialCreateData
{
    public function __construct(
        public string $name,
        public AuthType $authType,
        public ?string $username = null,
        public ?string $secret = null,
        public ?array $extra = null,
        public bool $isDefault = true,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'auth_type' => $this->authType->value,
            'username' => $this->username,
            'secret' => $this->secret,
            'extra' => $this->extra,
            'is_default' => $this->isDefault,
        ];
    }
}
