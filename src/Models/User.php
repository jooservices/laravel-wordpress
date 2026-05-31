<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Models;

use Jooservices\LaravelWordPress\Enums\SyncStatus;

final class User extends GenericEntityModel
{
    protected $table = 'users';

    protected $casts = [
        'synced_at' => 'datetime',
        'last_pulled_at' => 'datetime',
        'last_pushed_at' => 'datetime',
        'conflicted_at' => 'datetime',
        'conflict_payload' => 'array',
        'raw_payload' => 'array',
        'sync_status' => SyncStatus::class,
        'registered_at' => 'datetime',
        'roles' => 'array',
        'capabilities' => 'array',
        'extra_capabilities' => 'array',
        'avatar_urls' => 'array',
        'meta' => 'array',
    ];
}
