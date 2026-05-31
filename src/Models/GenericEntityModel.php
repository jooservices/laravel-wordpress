<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jooservices\LaravelWordPress\Enums\SyncStatus;

abstract class GenericEntityModel extends BaseModel
{
    protected $casts = [
        'title' => 'array',
        'content' => 'array',
        'excerpt' => 'array',
        'meta' => 'array',
        'synced_at' => 'datetime',
        'last_pulled_at' => 'datetime',
        'last_pushed_at' => 'datetime',
        'conflicted_at' => 'datetime',
        'conflict_payload' => 'array',
        'raw_payload' => 'array',
        'sync_status' => SyncStatus::class,
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
