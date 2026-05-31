<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Media;

use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\MediaStorage;
use Jooservices\LaravelWordPress\Services\Shared\ResourceService;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class MediaService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function records(): ResourceService
    {
        return $this->resources->make($this->site, 'media');
    }

    public function pull(array $query = []): object
    {
        return $this->records()->pull($query);
    }

    public function downloadFile(MediaItem $media): MediaItem
    {
        return app(MediaStorage::class)->download($media);
    }

    public function uploadFile(MediaItem $media): mixed
    {
        return $this->records()->push($media, true);
    }

    public function deleteLocalFile(MediaItem $media): bool
    {
        return app(MediaStorage::class)->deleteLocalFile($media);
    }

    public function checkRecordSyncState(MediaItem $media): object
    {
        return $this->records()->checkSyncState($media);
    }

    public function checkFileSyncState(MediaItem $media): object
    {
        return $this->records()->checkSyncState($media);
    }

    public function resolveRecordConflictUsingLocal(MediaItem $media): object
    {
        return $this->records()->resolveConflictUsingLocal($media);
    }

    public function resolveRecordConflictUsingRemote(MediaItem $media): object
    {
        return $this->records()->resolveConflictUsingRemote($media);
    }

    public function resolveFileConflictUsingLocal(MediaItem $media): object
    {
        return $this->records()->resolveConflictUsingLocal($media);
    }

    public function resolveFileConflictUsingRemote(MediaItem $media): object
    {
        return $this->records()->resolveConflictUsingRemote($media);
    }
}
