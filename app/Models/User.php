<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'address',
        'avatar',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function managedGroups(): HasMany
    {
        return $this->hasMany(ArisanGroup::class, 'admin_id');
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function wonRounds(): HasMany
    {
        return $this->hasMany(ArisanRound::class, 'winner_user_id');
    }

    public function hostRounds(): HasMany
    {
        return $this->hasMany(ArisanRound::class, 'host_user_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'user_id');
    }

    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}
