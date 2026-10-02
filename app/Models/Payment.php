<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'group_id',
        'user_id',
        'amount',
        'penalty_amount',
        'total_amount',
        'due_date',
        'payment_status',
        'payment_method',
        'proof_image',
        'paid_at',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'user_notes',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'penalty_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(ArisanRound::class, 'round_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ArisanGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function calculatePenalty(): void
    {
        if ($this->payment_status === 'paid') {
            return;
        }

        $group = $this->group;
        if (!$group || $group->late_fee_per_day <= 0) {
            return;
        }

        $today = now()->startOfDay();
        $graceDate = $this->due_date->copy()->addDays($group->grace_period_days)->startOfDay();

        if ($today->gt($graceDate)) {
            $daysLate = $graceDate->diffInDays($today);
            $this->penalty_amount = $daysLate * (float)$group->late_fee_per_day;
            $this->total_amount = (float)$this->amount + $this->penalty_amount;
            if ($this->payment_status === 'unpaid') {
                $this->payment_status = 'late';
            }
            $this->save();
        }
    }
}
