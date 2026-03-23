<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailProviderEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'event_type',
        'sns_message_id',
        'ses_message_id',
        'topic_arn',
        'campaign_id',
        'recipient_id',
        'subscriber_id',
        'email',
        'occurred_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(CampaignRecipient::class, 'recipient_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }
}
