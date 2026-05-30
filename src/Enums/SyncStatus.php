<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Enums;

enum SyncStatus: string
{
    case LocalOnly = 'local_only';
    case Synced = 'synced';
    case Dirty = 'dirty';
    case Syncing = 'syncing';
    case Failed = 'failed';
    case Conflict = 'conflict';
    case DeletedLocal = 'deleted_local';
    case DeletedRemote = 'deleted_remote';
    case DeletedBoth = 'deleted_both';
}
