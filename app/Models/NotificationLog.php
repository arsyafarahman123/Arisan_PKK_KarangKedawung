<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'round_id',
        'type',
        'title',
        'message',
        'channel',
        'status',
        'sent_at',
        'read_at',
        'whatsapp_link',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ArisanGroup::class, 'group_id');
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(ArisanRound::class, 'round_id');
    }
}
