<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DeliveryServer;

class AutoResponderStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'auto_responder_id',
        'step_order',
        'name',
        'template_id',
        'delivery_server_id',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'delay_value',
        'delay_unit',
        'status',
        'html_content',
        'plain_text_content',
        'template_data',
        'track_opens',
        'track_clicks',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'track_opens' => 'boolean',
            'track_clicks' => 'boolean',
        ];
    }

    public function autoResponder(): BelongsTo
    {
        return $this->belongsTo(AutoResponder::class, 'auto_responder_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function deliveryServer(): BelongsTo
    {
        return $this->belongsTo(DeliveryServer::class, 'delivery_server_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
