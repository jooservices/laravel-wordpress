<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Media;

use Illuminate\Database\Eloquent\Collection;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class MediaRecordService
{
    private ResourceService $resources;

    public function __construct(
        private readonly Site $site,
        ResourceServiceFactory $resources,
    ) {
        $this->resources = $resources->make($this->site, 'media');
    }

    public function listLocal(): Collection
    {
        return $this->resources->listLocal();
    }

    public function getLocal(int|string $id): ?MediaItem
    {
        $media = $this->resources->getLocal($id);

        return $media instanceof MediaItem ? $media : null;
    }

    public function createLocal(array $payload): MediaItem
    {
        return $this->ensureMediaItem($this->resources->createLocal($payload));
    }

    public function updateLocal(MediaItem $media, array $payload): MediaItem
    {
        return $this->ensureMediaItem($this->resources->updateLocal($media, $payload));
    }

    public function deleteLocal(MediaItem $media): bool
    {
        return $this->resources->deleteLocal($media);
    }

    public function listRemote(array $query = []): array
    {
        return $this->resources->listRemote($query);
    }

    public function getRemote(int|string $remoteId): mixed
    {
        return $this->resources->getRemote($remoteId);
    }

    public function createRemote(array $payload): mixed
    {
        throw new WordPressException('Remote media record creation requires file bytes. Use media()->files()->upload(...) instead.');
    }

    public function updateRemote(int|string $remoteId, array $payload): mixed
    {
        throw new WordPressException('Remote media record update is not exposed by the installed WordPress SDK media service.');
    }

    public function deleteRemote(int|string $remoteId, bool $force = false): mixed
    {
        return $this->resources->deleteRemote($remoteId, $force);
    }

    public function pull(array $query = []): object
    {
        return $this->resources->pull($query);
    }

    public function pullOne(int|string $remoteId): object
    {
        return $this->resources->pullOne($remoteId);
    }

    public function push(MediaItem $media, bool $force = false): object
    {
        return $this->resources->push($media, $force);
    }

    public function sync(array $query = []): object
    {
        return $this->resources->sync($query);
    }

    public function checkSyncState(MediaItem $media): object
    {
        return $this->resources->checkSyncState($media);
    }

    public function resolveConflictUsingLocal(MediaItem $media): object
    {
        return $this->resources->resolveConflictUsingLocal($media);
    }

    public function resolveConflictUsingRemote(MediaItem $media): object
    {
        return $this->resources->resolveConflictUsingRemote($media);
    }

    public function resolveConflictManually(MediaItem $media, array $payload): MediaItem
    {
        return $this->ensureMediaItem($this->resources->resolveConflictManually($media, $payload));
    }

    private function ensureMediaItem(mixed $model): MediaItem
    {
        if ($model instanceof MediaItem) {
            return $model;
        }

        throw new WordPressException('Expected media record local operation to return a MediaItem.');
    }
}
