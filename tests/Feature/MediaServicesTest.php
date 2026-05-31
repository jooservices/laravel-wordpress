<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Jooservices\LaravelWordPress\DTOs\Media\MediaUploadData;
use Jooservices\LaravelWordPress\DTOs\Sites\SiteCreateData;
use Jooservices\LaravelWordPress\Enums\FileSyncStatus;
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

    public function test_remote_media_record_create_points_callers_to_file_upload(): void
    {
        $site = WordPress::sites()->create(new SiteCreateData('Media', 'https://example.com'));

        $this->expectException(WordPressException::class);
        $this->expectExceptionMessage('Use media()->files()->upload(...)');

        WordPress::site($site)->media()->records()->createRemote(['title' => 'No bytes']);
    }
}
