<?php

namespace App\Models;

use App\Casts\UnixTimestampCast;
use App\Models\Traits\HasTableName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasTableName;

    protected $fillable = [
        'name',
        'slug',
        'bounded_context',
        'description',
        'is_system',
        'created_at',
        'updated_at',
    ];

    /**
     * Desactivamos timestamps automáticos de Laravel porque ahora son enteros.
     */
    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $now = time();
            if (empty($model->created_at)) {
                $model->created_at = $now;
            }
            if (empty($model->updated_at)) {
                $model->updated_at = $now;
            }
        });

        static::updating(function ($model) {
            $model->updated_at = time();
        });
    }

    protected $casts = [
        'is_system' => 'boolean',
        'created_at' => UnixTimestampCast::class,
        'updated_at' => UnixTimestampCast::class,
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
