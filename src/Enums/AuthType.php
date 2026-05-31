<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Enums;

enum AuthType: string
{
    case ApplicationPassword = 'application_password';
    case Basic = 'basic';
    case BearerToken = 'bearer_token';
}
