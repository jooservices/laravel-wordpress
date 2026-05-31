<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Media;

use Jooservices\LaravelWordPress\DTOs\Media\MediaUploadData;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Services\Shared\ResourceServiceFactory;

final class MediaService
{
    public function __construct(
        private readonly Site $site,
        private readonly ResourceServiceFactory $resources,
    ) {}

    public function records(): MediaRecordService
    {
        return new MediaRecordService($this->site, $this->resources);
    }

    public function files(): MediaFileService
    {
        return app(MediaFileService::class, ['site' => $this->site]);
    }

    public function pull(array $query = []): object
    {
        return $this->records()->pull($query);
    }

    public function downloadFile(MediaItem $media): MediaItem
    {
        return $this->files()->download($media);
    }

    public function uploadFile(MediaUploadData $data): MediaItem
    {
        return $this->files()->upload($data);
    }

    public function deleteLocalFile(MediaItem $media): bool
    {
        return $this->files()->deleteLocal($media);
    }

    public function checkRecordSyncState(MediaItem $media): object
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
}
