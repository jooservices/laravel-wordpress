<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Repositories;

use Jooservices\LaravelWordPress\Models\Site;

final class SiteRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Site);
    }
}
