<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Services\Media;

function mime_content_type(string $filename): string|false
{
    if (str_contains($filename, 'media-unknown-mime-')) {
        return false;
    }

    return \mime_content_type($filename);
}

namespace Jooservices\LaravelWordPress\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jooservices\LaravelWordPress\DTOs\Media\MediaUploadData;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\FileSyncStatus;
use Jooservices\LaravelWordPress\Exceptions\RemoteNotConfiguredException;
use Jooservices\LaravelWordPress\Exceptions\WordPressException;
use Jooservices\LaravelWordPress\Facades\WordPress;
use Jooservices\LaravelWordPress\Models\MediaItem;
use Jooservices\LaravelWordPress\Services\Media\MediaFileService;
use Jooservices\LaravelWordPress\Services\Media\MediaRecordService;
use Jooservices\LaravelWordPress\Tests\TestCase;

final class MediaServicesTest extends TestCase
{
    public function test_media_service_exposes_record_and_file_services(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $media = WordPress::site($site)->media();

        self::assertInstanceOf(MediaRecordService::class, $media->records());
        self::assertInstanceOf(MediaFileService::class, $media->files());
    }

    public function test_media_record_service_manages_local_records(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $records = WordPress::site($site)->media()->records();

        $media = $records->createLocal([
            'title' => 'Local media',
            'source_url' => 'https://example.com/image.jpg',
        ]);
        $updated = $records->updateLocal($media, ['alt_text' => 'Alt']);

        self::assertInstanceOf(MediaItem::class, $media);
        self::assertInstanceOf(MediaItem::class, $updated);
        self::assertSame('Alt', $updated->alt_text);
        self::assertTrue($records->deleteLocal($updated));
    }

    public function test_media_file_download_updates_local_file_fields(): void
    {
        Storage::fake('local');
        Http::fake([
            'https://example.com/image.jpg' => Http::response('image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $media = WordPress::site($site)->media()->records()->createLocal([
            'remote_id' => 123,
            'source_url' => 'https://example.com/image.jpg',
            'remote_file_name' => 'image.jpg',
            'media_type' => 'image',
        ]);

        $downloaded = WordPress::site($site)->media()->files()->download($media);

        self::assertSame(FileSyncStatus::Synced, $downloaded->file_sync_status);
        self::assertSame('image/jpeg', $downloaded->mime_type);
        self::assertNotNull($downloaded->local_path);
        Storage::disk('local')->assertExists($downloaded->local_path);
    }

    public function test_delete_local_file_only_clears_local_file_state(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('wordpress/media/test.txt', 'bytes');

        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $media = WordPress::site($site)->media()->records()->createLocal([
            'remote_id' => 123,
            'source_url' => 'https://example.com/test.txt',
            'local_disk' => 'local',
            'local_path' => 'wordpress/media/test.txt',
        ]);

        self::assertTrue(WordPress::site($site)->media()->files()->deleteLocal($media));
        self::assertSame(123, $media->refresh()->remote_id);
        self::assertNull($media->local_path);
        Storage::disk('local')->assertMissing('wordpress/media/test.txt');
    }

    public function test_upload_file_alias_requires_media_upload_data(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));

        self::assertSame(
            MediaUploadData::class,
            (new \ReflectionMethod(WordPress::site($site)->media(), 'uploadFile'))->getParameters()[0]->getType()?->getName(),
        );
    }

    public function test_upload_file_alias_delegates_to_file_upload_service(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $method = new \ReflectionMethod(WordPress::site($site)->media(), 'uploadFile');
        $source = file($method->getFileName() ?: '', flags: FILE_IGNORE_NEW_LINES);
        $body = implode("\n", array_slice($source ?: [], $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1));

        self::assertStringContainsString('$this->files()->upload($data)', $body);
        self::assertStringNotContainsString('records()->push', $body);
    }

    public function test_media_service_does_not_expose_file_conflict_methods_without_file_conflict_lifecycle(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $media = WordPress::site($site)->media();

        self::assertFalse(method_exists($media, 'checkFileSyncState'));
        self::assertFalse(method_exists($media, 'resolveFileConflictUsingLocal'));
        self::assertFalse(method_exists($media, 'resolveFileConflictUsingRemote'));
    }

    public function test_remote_media_record_create_points_callers_to_file_upload(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));

        $this->expectException(WordPressException::class);
        $this->expectExceptionMessage('Use media()->files()->upload(...)');

        WordPress::site($site)->media()->records()->createRemote(['title' => 'No bytes']);
    }

    public function test_remote_media_record_update_reports_sdk_limitation(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));

        $this->expectException(WordPressException::class);
        $this->expectExceptionMessage('Remote media record update is not exposed');

        WordPress::site($site)->media()->records()->updateRemote(123, ['title' => 'No SDK support']);
    }

    public function test_media_file_upload_validates_missing_file(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));

        $this->expectException(WordPressException::class);
        $this->expectExceptionMessage('does not exist');

        WordPress::site($site)->media()->files()->upload(new MediaUploadData(
            path: sys_get_temp_dir().'/missing-media-upload-'.bin2hex(random_bytes(4)).'.txt',
        ));
    }

    public function test_media_file_upload_validates_non_file_source(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $directory = sys_get_temp_dir().'/media-upload-directory-'.bin2hex(random_bytes(4));
        mkdir($directory);

        try {
            $this->expectException(WordPressException::class);
            $this->expectExceptionMessage('must be a file');

            WordPress::site($site)->media()->files()->upload(new MediaUploadData(path: $directory));
        } finally {
            rmdir($directory);
        }
    }

    public function test_media_file_upload_validates_empty_file(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $path = tempnam(sys_get_temp_dir(), 'media-empty-');

        try {
            $this->expectException(WordPressException::class);
            $this->expectExceptionMessage('is empty');

            WordPress::site($site)->media()->files()->upload(new MediaUploadData(path: $path));
        } finally {
            unlink($path);
        }
    }

    public function test_media_file_upload_rejects_files_larger_than_configured_max_size(): void
    {
        config()->set('wordpress.media.max_file_size', 4);

        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $path = tempnam(sys_get_temp_dir(), 'media-large-');
        file_put_contents($path, 'larger than four bytes');

        try {
            $this->expectException(WordPressException::class);
            $this->expectExceptionMessage('exceeds maximum size');

            WordPress::site($site)->media()->files()->upload(new MediaUploadData(
                path: $path,
                mimeType: 'text/plain',
            ));
        } finally {
            unlink($path);
        }
    }

    public function test_media_file_upload_validates_disallowed_mime_type(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $path = tempnam(sys_get_temp_dir(), 'media-mime-');
        file_put_contents($path, 'not an image');

        try {
            $this->expectException(WordPressException::class);
            $this->expectExceptionMessage('is not allowed');

            WordPress::site($site)->media()->files()->upload(new MediaUploadData(
                path: $path,
                mimeType: 'application/x-not-allowed',
            ));
        } finally {
            unlink($path);
        }
    }

    public function test_media_file_upload_rejects_unknown_mime_type_when_allow_list_is_configured(): void
    {
        config()->set('wordpress.media.allowed_mime_types', ['image/png']);

        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $path = tempnam(sys_get_temp_dir(), 'media-unknown-mime-');
        file_put_contents($path, 'not an image');

        try {
            $this->expectException(WordPressException::class);
            $this->expectExceptionMessage('could not be detected');

            WordPress::site($site)->media()->files()->upload(new MediaUploadData(path: $path));
        } finally {
            unlink($path);
        }
    }

    public function test_media_file_upload_accepts_allowed_mime_type_before_remote_upload(): void
    {
        config()->set('wordpress.media.allowed_mime_types', ['text/plain']);

        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));
        $path = tempnam(sys_get_temp_dir(), 'media-allowed-mime-');
        file_put_contents($path, 'allowed text upload');

        try {
            $this->expectException(RemoteNotConfiguredException::class);

            WordPress::site($site)->media()->files()->upload(new MediaUploadData(
                path: $path,
                mimeType: 'text/plain',
            ));
        } finally {
            unlink($path);
        }
    }
}
