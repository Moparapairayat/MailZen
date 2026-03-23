<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoResponderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_responder_run_id',
        'auto_responder_id',
        'auto_responder_step_id',
        'step_order',
        'subscriber_id',
        'list_id',
        'status',
        'triggered_at',
        'scheduled_for',
        'sent_at',
        'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'scheduled_for' => 'datetime',
            'sent_at' => 'datetime',
            'step_order' => 'integer',
        ];
    }

    public function autoResponder(): BelongsTo
    {
        return $this->belongsTo(AutoResponder::class, 'auto_responder_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(AutoResponderStep::class, 'auto_responder_step_id');
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(AutoResponderRun::class, 'auto_responder_run_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }
}
