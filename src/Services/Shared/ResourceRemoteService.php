<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Resources\Contracts\ResourceDefinition;
use Jooservices\LaravelWordPress\Services\RemoteClientFactory;

final class ResourceRemoteService
{
    public function __construct(
        private readonly RemoteClientFactory $clients,
    ) {}

    public function list(ResourceDefinition $definition, Site $site, array $query = []): array
    {
        $service = $definition->remoteService($this->clients->make($site));
        if (method_exists($service, 'all')) {
            return $service->all($query);
        }
        if (method_exists($service, 'list')) {
            $result = $service->list($query);

            return is_array($result) ? $result : (method_exists($result, 'items') ? $result->items() : iterator_to_array($result));
        }
        if (method_exists($service, 'get')) {
            return $service->get($definition->key(), $query);
        }

        throw new WordPressException("Resource [{$definition->key()}] does not support remote list.");
    }

    public function get(ResourceDefinition $definition, Site $site, int|string $remoteId): mixed
    {
        $service = $definition->remoteService($this->clients->make($site));

        return $service->get($remoteId);
    }

    public function create(ResourceDefinition $definition, Site $site, array $payload): mixed
    {
        if (! $definition->supportsCreate()) {
            throw new WordPressException("Resource [{$definition->key()}] does not support remote create.");
        }

        return $definition->remoteService($this->clients->make($site))->create($payload);
    }

    public function update(ResourceDefinition $definition, Site $site, int|string $remoteId, array $payload): mixed
    {
        if (! $definition->supportsUpdate()) {
            throw new WordPressException("Resource [{$definition->key()}] does not support remote update.");
        }

        return $definition->remoteService($this->clients->make($site))->update($remoteId, $payload);
    }

    public function delete(ResourceDefinition $definition, Site $site, int|string $remoteId, bool $force = false): mixed
    {
        if (! $definition->supportsDelete()) {
            throw new WordPressException("Resource [{$definition->key()}] does not support remote delete.");
        }

        return $definition->remoteService($this->clients->make($site))->delete($remoteId, $force);
    }
}
