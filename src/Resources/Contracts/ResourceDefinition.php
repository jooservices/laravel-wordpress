<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Contracts;

use Illuminate\Database\Eloquent\Model;
use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Services\RemoteClient;

interface ResourceDefinition
{
    public function key(): string;

    public function entityType(): EntityType;

    public function table(): string;

    public function modelClass(): string;

    public function remoteService(RemoteClient $client): object;

    public function remoteIdKey(): string;

    public function supportsCreate(): bool;

    public function supportsUpdate(): bool;

    public function supportsDelete(): bool;

    public function toLocalPayload(array|object $remote): array;

    public function toRemotePayload(Model $model): array;

    public function syncPayload(Model|array $payload): array;
}
