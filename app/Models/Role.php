<?php

namespace App\Models;

use App\Casts\UnixTimestampCast;
use App\Models\Traits\HasTableName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasTableName;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'hierarchy',
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
        'hierarchy' => 'integer',
        'created_at' => UnixTimestampCast::class,
        'updated_at' => UnixTimestampCast::class,
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }
}
