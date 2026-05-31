<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Repositories;

use Jooservices\LaravelWordPress\Models\MediaItem;

final class MediaRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new MediaItem);
    }
}
