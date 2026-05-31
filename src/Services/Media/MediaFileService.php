<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Media;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Jooservices\LaravelWordPress\DTOs\Media\MediaUploadData;
use Jooservices\LaravelWordPress\Enums\FileSyncStatus;
use Jooservices\LaravelWordPress\Enums\SyncStatus;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Models\Site;
use Jooservices\LaravelWordPress\Resources\Media\MediaResource;
use Jooservices\LaravelWordPress\Services\RemoteClientFactory;
use Jooservices\LaravelWordPress\Services\Shared\MediaStorage;
use Jooservices\LaravelWordPress\Services\Shared\PayloadHasher;

final class MediaFileService
{
    public function __construct(
        private readonly Site $site,
        private readonly MediaStorage $storage,
        private readonly RemoteClientFactory $clients,
        private readonly PayloadHasher $hasher,
        private readonly MediaResource $resource = new MediaResource,
    ) {}

    public function download(MediaItem $media, ?string $disk = null): MediaItem
    {
        return $this->storage->download($media, $disk);
    }

    public function upload(MediaUploadData $data): MediaItem
    {
        $source = $this->resolveSourcePath($data);
        $attributes = $data->toRemotePayload();

        if ($data->filename !== null) {
            $attributes['filename'] = $data->filename;
        }

        $remote = $this->clients->make($this->site)->sdk()->media()->upload($source, $attributes);
        $payload = $this->resource->toLocalPayload($remote);
        $model = $this->findOrCreateLocal($payload['remote_id'] ?? null);
        $hash = $this->hasher->hash($this->resource->syncPayload($payload));
        $now = Carbon::now();

        $model->fill($payload + [
            'sync_status' => SyncStatus::Synced,
            'remote_hash' => $hash,
            'local_hash' => $hash,
            'synced_at' => $now,
            'last_pushed_at' => $now,
            'local_disk' => $data->disk,
            'local_path' => $data->diskPath,
            'file_name' => $data->filename ?? basename($source),
            'file_size' => filesize($source) ?: null,
            'checksum' => hash_file('sha256', $source) ?: null,
            'file_hash' => hash_file('sha256', $source) ?: null,
            'file_sync_status' => FileSyncStatus::Synced,
            'file_synced_at' => $now,
            'last_file_uploaded_at' => $now,
        ]);
        $model->save();

        return $model;
    }

    public function deleteLocal(MediaItem $media): bool
    {
        return $this->storage->deleteLocalFile($media);
    }

    public function localExists(MediaItem $media): bool
    {
        if ($media->local_disk === null || $media->local_path === null) {
            return false;
        }

        return Storage::disk($media->local_disk)->exists($media->local_path);
    }

    private function resolveSourcePath(MediaUploadData $data): string
    {
        if ($data->path !== null) {
            return $data->path;
        }

        if ($data->disk !== null && $data->diskPath !== null) {
            $path = Storage::disk($data->disk)->path($data->diskPath);
            if (! is_file($path)) {
                throw new WordPressException('Media upload source file does not exist on the configured disk.');
            }

            return $path;
        }

        throw new WordPressException('Media upload requires a local path or disk and disk path.');
    }

    private function findOrCreateLocal(int|string|null $remoteId): MediaItem
    {
        $media = $remoteId === null ? null : MediaItem::query()
            ->where('site_id', $this->site->getKey())
            ->where('remote_id', $remoteId)
            ->first();

        if ($media instanceof MediaItem) {
            return $media;
        }

        $media = new MediaItem;
        $media->site()->associate($this->site);

        return $media;
    }
}
