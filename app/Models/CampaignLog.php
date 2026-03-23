<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'recipient_id',
        'event',
        'meta',
        'ip_address',
        'user_agent',
        'url',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
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

    public static function logEvent(
        int $campaignId,
        string $event,
        ?int $recipientId = null,
        array $meta = [],
        ?string $ipAddress = null,
        ?string $userAgent = null,
        ?string $url = null,
        ?string $errorMessage = null
    ): self {
        return self::create([
            'campaign_id' => $campaignId,
            'recipient_id' => $recipientId,
            'event' => $event,
            'meta' => $meta,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'url' => $url,
            'error_message' => $errorMessage,
        ]);
    }
}

