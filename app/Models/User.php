<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'is_active',
        'is_admin',
        'accepted_terms',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_admin' => 'boolean',
        'accepted_terms' => 'boolean',
        'uuid' => 'string',
    ];

    /**
     * Boot the model and generate UUID on creation.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::orderedUuid();
            }
        });
    }

    /**
     * Get the time entries for the user.
     */
    public function timeEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Get the open time entry for the user.
     */
    public function openTimeEntry(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TimeEntry::class)->whereNull('salida');
    }

    /**
     * Get the holiday requests for the user.
     */
    public function holidayRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HolidayRequest::class);
    }
}
