<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Enums;

enum ConflictStrategy: string
{
    case MarkConflict = 'mark_conflict';
    case LocalWins = 'local_wins';
    case RemoteWins = 'remote_wins';
    case Throw = 'throw';
}
