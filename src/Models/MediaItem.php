<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jooservices\LaravelWordPress\Enums\FileSyncStatus;
use Jooservices\LaravelWordPress\Enums\RecordSyncStatus;

final class MediaItem extends BaseModel
{
    protected $table = 'media_items';

    protected $casts = [
        'guid' => 'array',
        'media_details' => 'array',
        'missing_image_sizes' => 'array',
        'meta' => 'array',
        'date' => 'datetime',
        'date_gmt' => 'datetime',
        'modified' => 'datetime',
        'modified_gmt' => 'datetime',
        'record_sync_status' => RecordSyncStatus::class,
        'record_synced_at' => 'datetime',
        'last_record_pulled_at' => 'datetime',
        'last_record_pushed_at' => 'datetime',
        'file_sync_status' => FileSyncStatus::class,
        'file_synced_at' => 'datetime',
        'last_file_downloaded_at' => 'datetime',
        'last_file_uploaded_at' => 'datetime',
        'record_conflict_payload' => 'array',
        'file_conflict_payload' => 'array',
        'record_conflicted_at' => 'datetime',
        'file_conflicted_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
