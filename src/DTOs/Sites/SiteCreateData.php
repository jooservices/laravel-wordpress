<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\DTOs\Sites;

final readonly class SiteCreateData
{
    public function __construct(
        public string $name,
        public string $baseUrl,
        public ?string $restApiBaseUrl = null,
        public string $status = 'active',
        public ?array $settings = null,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'base_url' => rtrim($this->baseUrl, '/'),
            'rest_api_base_url' => $this->restApiBaseUrl !== null ? rtrim($this->restApiBaseUrl, '/') : null,
            'status' => $this->status,
            'settings' => $this->settings,
        ];
    }
}
