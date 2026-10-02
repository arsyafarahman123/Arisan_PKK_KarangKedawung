<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArisanRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'round_number',
        'due_date',
        'draw_date',
        'host_user_id',
        'host_location',
        'winner_user_id',
        'winning_amount',
        'prize_disbursed',
        'disbursed_at',
        'disbursement_proof',
        'disbursement_notes',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'draw_date' => 'date',
            'disbursed_at' => 'datetime',
            'winning_amount' => 'decimal:2',
            'prize_disbursed' => 'boolean',
            'round_number' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ArisanGroup::class, 'group_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'round_id');
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class, 'round_id');
    }

    public function getPaidMembersCountAttribute(): int
    {
        return $this->payments()->where('payment_status', 'paid')->count();
    }

    public function getTotalMembersCountAttribute(): int
    {
        return $this->payments()->count();
    }

    public function getIsReadyForDrawAttribute(): bool
    {
        if ($this->status === 'completed') {
            return false;
        }

        if ($this->group->only_paid_can_win) {
            // Check if at least some unpaid eligible members exist
            return true;
        }

        return true;
    }
}
