<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoResponderRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_responder_id',
        'subscriber_id',
        'list_id',
        'status',
        'triggered_at',
        'next_step_order',
        'next_scheduled_for',
        'last_sent_at',
        'completed_at',
        'stopped_at',
        'stop_reason',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'next_scheduled_for' => 'datetime',
            'last_sent_at' => 'datetime',
            'completed_at' => 'datetime',
            'stopped_at' => 'datetime',
            'locked_at' => 'datetime',
            'next_step_order' => 'integer',
        ];
    }

    public function autoResponder(): BelongsTo
    {
        return $this->belongsTo(AutoResponder::class, 'auto_responder_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
