<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Facades;

use Illuminate\Support\Facades\Facade;

final class WordPress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-wordpress';
    }
}
