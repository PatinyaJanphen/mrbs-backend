<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    public const ROLE_SUPER_ADMIN = 0;
    public const ROLE_ADMIN = 1;
    public const ROLE_STAFF = 2;
    public const ROLE_USER = 3;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, \App\Traits\UserStamps;

    protected $fillable = [
        'name',
        'email',
        'google_id',
        'avatar',
        'google_access_token',
        'google_refresh_token',
        'role',
        'is_active',
        'password',
        'phone',
        'department',
    ];

    protected $hidden = [
        'password',
        'google_access_token',
        'google_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => 'integer',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function approvedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'approved_by');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
