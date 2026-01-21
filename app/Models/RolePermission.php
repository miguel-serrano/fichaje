<?php

namespace App\Models;

use App\Casts\UnixTimestampCast;
use App\Models\Traits\HasTableName;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RolePermission extends Pivot
{
    use HasTableName;

    protected $table = 'role_permission';

    public $incrementing = false;

    /**
     * Desactivamos timestamps automáticos de Laravel porque ahora son enteros.
     */
    public $timestamps = false;

    protected $casts = [
        'created_at' => UnixTimestampCast::class,
        'updated_at' => UnixTimestampCast::class,
    ];
}
