<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'subject',
        'html_content',
        'plain_text_content',
        'split_percentage',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'unsubscribed_count',
        'open_rate',
        'click_rate',
        'bounce_rate',
        'is_winner',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'split_percentage' => 'integer',
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'delivered_count' => 'integer',
            'opened_count' => 'integer',
            'clicked_count' => 'integer',
            'bounced_count' => 'integer',
            'unsubscribed_count' => 'integer',
            'open_rate' => 'decimal:2',
            'click_rate' => 'decimal:2',
            'bounce_rate' => 'decimal:2',
            'is_winner' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function calculateOpenRate(): float
    {
        if ($this->delivered_count == 0) {
            return 0;
        }
        return ($this->opened_count / $this->delivered_count) * 100;
    }

    public function calculateClickRate(): float
    {
        if ($this->delivered_count == 0) {
            return 0;
        }
        return ($this->clicked_count / $this->delivered_count) * 100;
    }

    public function updateStats(): void
    {
        $this->open_rate = $this->calculateOpenRate();
        $this->click_rate = $this->calculateClickRate();
        if ($this->sent_count > 0) {
            $this->bounce_rate = ($this->bounced_count / $this->sent_count) * 100;
        }
        $this->save();
    }
}
