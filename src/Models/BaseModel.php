<?php

declare(strict_types=1);

namespace Jooservices\LaravelWordPress\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function getTable(): string
    {
        $table = parent::getTable();
        $prefix = (string) config('wordpress.table_prefix', 'wp_');

        return str_starts_with($table, $prefix) ? $table : $prefix.$table;
    }
}
