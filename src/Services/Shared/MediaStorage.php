<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Shared;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jooservices\LaravelWordPress\Enums\FileSyncStatus;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Models\MediaItem;

final class MediaStorage
{
    public function download(MediaItem $media, ?string $disk = null): MediaItem
    {
        $url = (string) $media->source_url;
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new WordPressException('Media source URL must be http or https.');
        }

        $response = Http::timeout(30)->get($url);
        if (! $response->successful()) {
            throw new WordPressException('Unable to download media file.');
        }

        $contents = $response->body();
        $max = (int) config('wordpress.media.max_file_size', 50 * 1024 * 1024);
        if (strlen($contents) > $max) {
            throw new WordPressException('Media file exceeds configured maximum size.');
        }

        $mimeType = $response->header('Content-Type');
        $mimeType = is_string($mimeType) ? trim(explode(';', $mimeType)[0]) : null;
        $allowed = config('wordpress.media.allowed_mime_types', []);
        if ($mimeType !== null && is_array($allowed) && $allowed !== [] && ! in_array($mimeType, $allowed, true)) {
            throw new WordPressException('Media file MIME type is not allowed.');
        }

        $disk ??= (string) config('wordpress.media.disk', 'local');
        $path = $this->pathFor($media);
        $this->disk($disk)->put($path, $contents);

        $media->fill([
            'local_disk' => $disk,
            'local_path' => $path,
            'file_name' => basename($path),
            'file_size' => strlen($contents),
            'mime_type' => $mimeType ?? $media->mime_type,
            'checksum' => hash('sha256', $contents),
            'file_hash' => hash('sha256', $contents),
            'file_sync_status' => FileSyncStatus::Synced,
            'file_synced_at' => now(),
            'last_file_downloaded_at' => now(),
        ]);
        $media->save();

        return $media;
    }

    public function deleteLocalFile(MediaItem $media): bool
    {
        if ($media->local_disk === null || $media->local_path === null) {
            return false;
        }

        $deleted = $this->disk($media->local_disk)->delete($media->local_path);
        $media->fill([
            'local_path' => null,
            'file_sync_status' => FileSyncStatus::Missing,
        ])->save();

        return $deleted;
    }

    public function pathFor(MediaItem $media): string
    {
        $base = trim((string) config('wordpress.media.base_path', 'wordpress/media'), '/');
        $date = $media->date ?? now();
        $filename = Str::slug(pathinfo((string) ($media->remote_file_name ?: $media->file_name ?: 'media'), PATHINFO_FILENAME));
        $extension = pathinfo((string) ($media->remote_file_name ?: $media->file_name ?: 'file'), PATHINFO_EXTENSION);
        $safe = $filename.($extension !== '' ? '.'.strtolower($extension) : '');

        return "{$base}/site-{$media->site_id}/{$media->media_type}/{$date->format('Y')}/{$date->format('m')}/{$media->remote_id}-{$safe}";
    }

    private function disk(string $disk): Filesystem
    {
        return Storage::disk($disk);
    }
}
