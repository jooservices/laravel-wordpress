<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Resources\Users;

use Jooservices\LaravelWordPress\Enums\EntityType;
use Jooservices\LaravelWordPress\Models\ApplicationPassword;
use Jooservices\LaravelWordPress\Resources\BaseResourceDefinition;

final class ApplicationPasswordResource extends BaseResourceDefinition
{
    public function __construct()
    {
        parent::__construct('application-passwords', EntityType::ApplicationPassword, 'application_passwords', ApplicationPassword::class, 'applicationPasswords', true, true, true);
    }
}
