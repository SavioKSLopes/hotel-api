<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'hotel_id',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public const ROLE_OWNER = 'owner';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_RECEPTIONIST = 'receptionist';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function reserves(): HasMany
    {
        return $this->hasMany(Reserve::class);
    }

    public function canManageReserves(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_MANAGER, self::ROLE_RECEPTIONIST], true);
    }

    public function canManageHotelSettings(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_MANAGER], true);
    }

    public function canManageUsers(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_MANAGER], true);
    }

    public function canManagePayments(): bool
    {
        return in_array($this->role, [self::ROLE_OWNER, self::ROLE_MANAGER], true);
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }
}
