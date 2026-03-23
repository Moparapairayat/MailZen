<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BouncedEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bounce_server_id',
        'delivery_server_id',
        'campaign_id',
        'list_id',
        'subscriber_id',
        'recipient_id',
        'email',
        'bounce_server_username',
        'bounce_server_mailbox',
        'bounce_type',
        'bounce_code',
        'diagnostic_code',
        'reason',
        'raw_message',
        'last_bounced_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'last_bounced_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function bounceServer(): BelongsTo
    {
        return $this->belongsTo(BounceServer::class);
    }

    public function deliveryServer(): BelongsTo
    {
        return $this->belongsTo(DeliveryServer::class, 'delivery_server_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(CampaignRecipient::class, 'recipient_id');
    }
}
