<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Jooservices\LaravelWordPress\Enums\AuthType;

final class Credential extends BaseModel
{
    protected $table = 'credentials';

    protected $casts = [
        'auth_type' => AuthType::class,
        'secret' => 'encrypted',
        'extra' => 'array',
        'is_default' => 'boolean',
        'last_used_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
