<?php

namespace App\Models;

use App\Casts\UnixTimestampCast;
use App\Models\Traits\HasTableName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasTableName;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'array',
        'read_at' => UnixTimestampCast::class,
        'created_at' => UnixTimestampCast::class,
        'updated_at' => UnixTimestampCast::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => time()]);
    }

    public function isRead(): bool
    {
        return null !== $this->read_at;
    }

    public function getCreatedAtCarbon(): Carbon
    {
        return Carbon::createFromTimestamp($this->created_at);
    }
}
