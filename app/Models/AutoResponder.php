<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DeliveryServer;

class AutoResponder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'automation_id',
        'list_id',
        'delivery_server_id',
        'template_id',
        'name',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'trigger',
        'trigger_settings',
        'delay_value',
        'delay_unit',
        'status',
        'html_content',
        'plain_text_content',
        'template_data',
        'track_opens',
        'track_clicks',
        'sent_count',
        'opened_count',
        'clicked_count',
    ];

    protected function casts(): array
    {
        return [
            'trigger_settings' => 'array',
            'template_data' => 'array',
            'track_opens' => 'boolean',
            'track_clicks' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function deliveryServer(): BelongsTo
    {
        return $this->belongsTo(DeliveryServer::class, 'delivery_server_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(AutoResponderStep::class, 'auto_responder_id')->orderBy('step_order');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AutoResponderRun::class, 'auto_responder_id');
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
