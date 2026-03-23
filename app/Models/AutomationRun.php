<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'subscriber_id',
        'status',
        'trigger_event',
        'trigger_context',
        'current_node_id',
        'triggered_at',
        'next_scheduled_for',
        'locked_at',
    ];

    protected function casts(): array
    {
        return [
            'trigger_context' => 'array',
            'triggered_at' => 'datetime',
            'next_scheduled_for' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
