<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BounceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'bounce_server_id',
        'subscriber_id',
        'campaign_id',
        'list_id',
        'recipient_id',
        'email',
        'bounce_type',
        'bounce_code',
        'diagnostic_code',
        'reason',
        'raw_message',
        'message_id',
        'bounced_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'bounced_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function bounceServer(): BelongsTo
    {
        return $this->belongsTo(BounceServer::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(CampaignRecipient::class, 'recipient_id');
    }

    public function isHardBounce(): bool
    {
        return $this->bounce_type === 'hard';
    }

    public function isSoftBounce(): bool
    {
        return $this->bounce_type === 'soft';
    }
}
