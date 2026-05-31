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
        $fileSize = $this->fileSize($source);
        $this->ensureAllowedFileSize($fileSize);

        $fileHash = hash_file('sha256', $source);

        if ($fileHash === false) {
            throw new WordPressException('Unable to hash media upload source file.');
        }

        $mimeType = $this->mimeType($data, $source);
        $this->ensureAllowedMimeType($mimeType);

        $attributes = $data->toRemotePayload();

        $remote = $this->clients->make($this->site)->sdk()->media()->upload($source, $attributes);
        $payload = $this->resource->toLocalPayload($remote);
        $model = $this->findOrCreateLocal($payload['remote_id'] ?? null);
        $hash = $this->hasher->hash($this->resource->syncPayload($payload));
        $now = Carbon::now();
        $isStorageSource = $data->disk !== null && $data->diskPath !== null;

        $model->fill($payload + [
            'sync_status' => SyncStatus::Synced,
            'remote_hash' => $hash,
            'local_hash' => $hash,
            'synced_at' => $now,
            'last_pushed_at' => $now,
            'local_disk' => $isStorageSource ? $data->disk : null,
            'local_path' => $isStorageSource ? $data->diskPath : null,
            'file_name' => $data->filename ?? basename($source),
            'file_size' => $fileSize,
            'checksum' => $fileHash,
            'file_hash' => $fileHash,
            'mime_type' => $payload['mime_type'] ?? $mimeType,
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
            return $this->validatedSourcePath($data->path);
        }

        if ($data->disk !== null && $data->diskPath !== null) {
            return $this->validatedSourcePath(Storage::disk($data->disk)->path($data->diskPath));
        }

        throw new WordPressException('Media upload requires a local path or disk and disk path.');
    }

    private function validatedSourcePath(string $path): string
    {
        if (! file_exists($path)) {
            throw new WordPressException('Media upload source file does not exist.');
        }

        if (! is_file($path)) {
            throw new WordPressException('Media upload source path must be a file.');
        }

        if (! is_readable($path)) {
            throw new WordPressException('Media upload source file is not readable.');
        }

        if ($this->fileSize($path) <= 0) {
            throw new WordPressException('Media upload source file is empty.');
        }

        return $path;
    }

    private function fileSize(string $path): int
    {
        $size = filesize($path);

        if ($size === false) {
            throw new WordPressException('Unable to determine media upload source file size.');
        }

        return $size;
    }

    private function ensureAllowedFileSize(int $fileSize): void
    {
        $max = (int) config('wordpress.media.max_file_size', 50 * 1024 * 1024);

        if ($max > 0 && $fileSize > $max) {
            throw new WordPressException("Media upload source file exceeds maximum size of {$max} bytes.");
        }
    }

    private function mimeType(MediaUploadData $data, string $source): ?string
    {
        if ($data->mimeType !== null) {
            return $data->mimeType;
        }

        $mimeType = mime_content_type($source);

        return $mimeType === false ? null : $mimeType;
    }

    private function ensureAllowedMimeType(?string $mimeType): void
    {
        $allowed = config('wordpress.media.allowed_mime_types', []);

        if ($allowed === []) {
            return;
        }

        if ($mimeType === null || trim($mimeType) === '') {
            throw new WordPressException('Media upload MIME type could not be detected.');
        }

        if (in_array($mimeType, $allowed, true)) {
            return;
        }

        throw new WordPressException("Media upload MIME type [{$mimeType}] is not allowed.");
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
