<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTracking extends Model
{
    use HasFactory;

    protected $table = 'campaign_tracking';

    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'email',
        'event_type',
        'url',
        'ip_address',
        'user_agent',
        'bounce_reason',
        'complaint_reason',
        'event_at',
    ];

    protected function casts(): array
    {
        return [
            'event_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }
}

