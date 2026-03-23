<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionalEmail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'key',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'html_content',
        'plain_text_content',
        'template_variables',
        'status',
        'description',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'track_opens',
        'track_clicks',
    ];

    protected function casts(): array
    {
        return [
            'template_variables' => 'array',
            'track_opens' => 'boolean',
            'track_clicks' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
