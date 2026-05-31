<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

final class PayloadHasher
{
    private const OMITTED_KEYS = [
        'id', 'site_id', 'created_at', 'updated_at', 'deleted_at',
        'synced_at', 'last_error', 'raw_payload',
    ];

    public function hash(array $payload): string
    {
        return hash('sha256', json_encode($this->normalize($payload), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function normalize(array $payload): array
    {
        foreach (self::OMITTED_KEYS as $key) {
            unset($payload[$key]);
        }

        ksort($payload);

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->normalize($value);
            }
        }

        return $payload;
    }
}
