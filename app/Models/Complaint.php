<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscriber_id',
        'campaign_id',
        'email',
        'source',
        'provider',
        'provider_message_id',
        'feedback_id',
        'complained_at',
        'raw_data',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'complained_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
