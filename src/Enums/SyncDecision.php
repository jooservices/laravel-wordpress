<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Enums;

enum SyncDecision: string
{
    case Noop = 'noop';
    case Pull = 'pull';
    case Push = 'push';
    case Conflict = 'conflict';
    case RemoteDeleted = 'remote_deleted';
    case LocalDeleted = 'local_deleted';
    case CreateRemote = 'create_remote';
    case CreateLocal = 'create_local';
}
