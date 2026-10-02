<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ArisanGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'admin_id',
        'contribution_amount',
        'period_type',
        'max_members',
        'start_date',
        'late_fee_per_day',
        'grace_period_days',
        'winner_determination',
        'only_paid_can_win',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'qris_image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contribution_amount' => 'decimal:2',
            'late_fee_per_day' => 'decimal:2',
            'start_date' => 'date',
            'only_paid_can_win' => 'boolean',
            'max_members' => 'integer',
            'grace_period_days' => 'integer',
        ];
    }

    protected static function booted()
    {
        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name) . '-' . Str::random(5);
            }
        });
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id');
    }

    public function activeMembers(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id')->where('is_active', true);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(ArisanRound::class, 'group_id')->orderBy('round_number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'group_id');
    }

    public function currentRound()
    {
        return $this->rounds()->where('status', 'ongoing')->first() 
            ?? $this->rounds()->where('status', 'pending')->first();
    }

    public function getTotalPotAttribute(): float
    {
        return (float)$this->contribution_amount * $this->members()->count();
    }
}
