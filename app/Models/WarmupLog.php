<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarmupLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_warmup_id',
        'send_date',
        'day_number',
        'target_volume',
        'sent_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'complained_count',
        'open_rate',
        'click_rate',
        'bounce_rate',
        'status',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'send_date' => 'date',
            'open_rate' => 'decimal:2',
            'click_rate' => 'decimal:2',
            'bounce_rate' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function emailWarmup(): BelongsTo
    {
        return $this->belongsTo(EmailWarmup::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(WarmupEmail::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->updateRates();
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'completed_at' => now(),
        ]);
    }

    public function updateRates(): void
    {
        if ($this->sent_count > 0) {
            $this->update([
                'open_rate' => round(($this->opened_count / $this->sent_count) * 100, 2),
                'click_rate' => round(($this->clicked_count / $this->sent_count) * 100, 2),
                'bounce_rate' => round(($this->bounced_count / $this->sent_count) * 100, 2),
            ]);
        }
    }

    public function incrementStat(string $type, int $count = 1): void
    {
        $field = match ($type) {
            'sent' => 'sent_count',
            'opened' => 'opened_count',
            'clicked' => 'clicked_count',
            'bounced' => 'bounced_count',
            'complained' => 'complained_count',
            default => null,
        };

        if ($field) {
            $this->increment($field, $count);
        }
    }
}
