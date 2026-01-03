<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    use HasFactory;

    protected $table = 'time_entries';

    protected $fillable = ['user_id', 'entrada', 'salida'];

    protected $casts = [
        'user_id' => 'string',
        'entrada' => 'datetime',
        'salida' => 'datetime',
    ];

    public $timestamps = true;

    /**
     * Get the user that owns the time entry.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
