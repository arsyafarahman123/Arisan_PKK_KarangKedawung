<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'join_date',
        'fixed_order_number',
        'has_won',
        'won_round_id',
        'notification_channel',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'has_won' => 'boolean',
            'is_active' => 'boolean',
            'fixed_order_number' => 'integer',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ArisanGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wonRound(): BelongsTo
    {
        return $this->belongsTo(ArisanRound::class, 'won_round_id');
    }
}
