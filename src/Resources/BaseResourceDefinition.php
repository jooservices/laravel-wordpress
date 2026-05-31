<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;
use Jooservices\LaravelWordPress\Services\RemoteClient;

abstract class BaseResourceDefinition implements ResourceDefinition
{
    public function __construct(
        private readonly string $key,
        private readonly EntityType $entityType,
        private readonly string $table,
        private readonly string $modelClass,
        private readonly string $sdkService,
        private readonly bool $create = true,
        private readonly bool $update = true,
        private readonly bool $delete = true,
        private readonly string $remoteIdKey = 'id',
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function entityType(): EntityType
    {
        return $this->entityType;
    }

    public function table(): string
    {
        return $this->table;
    }

    public function modelClass(): string
    {
        return $this->modelClass;
    }

    public function remoteService(RemoteClient $client): object
    {
        return $client->service($this->sdkService);
    }

    public function remoteIdKey(): string
    {
        return $this->remoteIdKey;
    }

    public function supportsCreate(): bool
    {
        return $this->create;
    }

    public function supportsUpdate(): bool
    {
        return $this->update;
    }

    public function supportsDelete(): bool
    {
        return $this->delete;
    }

    public function toLocalPayload(array|object $remote): array
    {
        $payload = $this->payloadArray($remote);
        $remoteId = $payload[$this->remoteIdKey] ?? null;

        return [
            'remote_id' => is_numeric($remoteId) ? (int) $remoteId : null,
            'raw_payload' => $payload,
        ] + $this->extractCommonFields($payload);
    }

    public function toRemotePayload(Model $model): array
    {
        return $this->syncPayload($model);
    }

    public function syncPayload(Model|array $payload): array
    {
        $data = $payload instanceof Model ? $payload->getAttributes() : $payload;
        unset(
            $data['id'], $data['site_id'], $data['remote_id'], $data['sync_status'],
            $data['synced_at'], $data['last_pulled_at'], $data['last_pushed_at'],
            $data['last_error'], $data['remote_hash'], $data['local_hash'],
            $data['conflict_payload'], $data['conflicted_at'], $data['raw_payload'],
            $data['created_at'], $data['updated_at'], $data['deleted_at'],
        );

        return array_filter($data, static fn (mixed $value): bool => $value !== null);
    }

    protected function payloadArray(array|object $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (method_exists($payload, 'toArray')) {
            return $payload->toArray();
        }

        return json_decode(json_encode($payload, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
    }

    protected function extractCommonFields(array $payload): array
    {
        $fields = [];
        foreach (['title', 'name', 'slug', 'status', 'type', 'link', 'description', 'content', 'excerpt', 'meta'] as $field) {
            if (array_key_exists($field, $payload)) {
                $fields[$field] = $payload[$field];
            }
        }

        return $fields;
    }
}
