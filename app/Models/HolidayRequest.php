<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\UnixTimestampCast;
use App\Models\Traits\HasTableName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolidayRequest extends Model
{
    use HasFactory;
    use HasTableName;

    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_date' => UnixTimestampCast::class,
        'end_date' => UnixTimestampCast::class,
        'created_at' => UnixTimestampCast::class,
        'updated_at' => UnixTimestampCast::class,
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return 'pending' === $this->status;
    }

    public function isApproved(): bool
    {
        return 'approved' === $this->status;
    }

    public function isRejected(): bool
    {
        return 'rejected' === $this->status;
    }

    /**
     * Obtiene la fecha de inicio como Carbon.
     */
    public function getStartDateCarbon(): Carbon
    {
        return Carbon::createFromTimestamp($this->start_date);
    }

    /**
     * Obtiene la fecha de fin como Carbon.
     */
    public function getEndDateCarbon(): Carbon
    {
        return Carbon::createFromTimestamp($this->end_date);
    }

    /**
     * Formatea la fecha de inicio para mostrar (solo fecha).
     */
    public function getStartDateFormattedAttribute(): string
    {
        return date('Y-m-d', $this->start_date);
    }

    /**
     * Formatea la fecha de fin para mostrar (solo fecha).
     */
    public function getEndDateFormattedAttribute(): string
    {
        return date('Y-m-d', $this->end_date);
    }
}
