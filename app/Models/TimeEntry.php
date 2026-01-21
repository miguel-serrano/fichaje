<?php

namespace App\Models;

use App\Casts\UnixTimestampCast;
use App\Models\Traits\HasTableName;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;
    use HasTableName;

    protected $table = 'time_entries';

    protected $fillable = [
        'user_id',
        'entrada',
        'salida',
        'auto_closed',
        'auto_close_reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'entrada' => UnixTimestampCast::class,
        'salida' => UnixTimestampCast::class,
        'auto_closed' => 'boolean',
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

    /**
     * Get the user that owns the time entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene la entrada como Carbon para compatibilidad.
     */
    public function getEntradaCarbon(): Carbon
    {
        return Carbon::createFromTimestamp($this->entrada);
    }

    /**
     * Obtiene la salida como Carbon para compatibilidad.
     */
    public function getSalidaCarbon(): ?Carbon
    {
        return null !== $this->salida ? Carbon::createFromTimestamp($this->salida) : null;
    }

    /**
     * Formatea la entrada para mostrar.
     */
    public function getEntradaFormattedAttribute(): string
    {
        return date('Y-m-d H:i:s', $this->entrada);
    }

    /**
     * Formatea la salida para mostrar.
     */
    public function getSalidaFormattedAttribute(): ?string
    {
        return null !== $this->salida ? date('Y-m-d H:i:s', $this->salida) : null;
    }
}
