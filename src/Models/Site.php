<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Models;

final class Site extends BaseModel
{
    protected $table = 'sites';

    protected $casts = [
        'settings' => 'array',
        'last_connected_at' => 'datetime',
        'last_synced_at' => 'datetime',
    ];
}
