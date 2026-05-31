<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Repositories;

use Jooservices\LaravelWordPress\Models\Credential;

final class CredentialRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Credential);
    }
}
