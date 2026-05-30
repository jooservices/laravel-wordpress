<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Enums;

enum EntityType: string
{
    case Site = 'site';
    case Credential = 'credential';
    case User = 'user';
    case Post = 'post';
    case Page = 'page';
    case Media = 'media';
    case Term = 'term';
    case Comment = 'comment';
    case RemoteResource = 'remote_resource';
}
