<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Repositories;

use Jooservices\LaravelWordPress\Models\User;

final class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new User);
    }
}
