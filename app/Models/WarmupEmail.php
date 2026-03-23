<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarmupEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'warmup_log_id',
        'email_warmup_id',
        'email',
        'subject',
        'message_id',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
        ];
    }

    public function warmupLog(): BelongsTo
    {
        return $this->belongsTo(WarmupLog::class);
    }

    public function emailWarmup(): BelongsTo
    {
        return $this->belongsTo(EmailWarmup::class);
    }

    public function markAsSent(?string $messageId = null): void
    {
        $this->update([
            'status' => 'sent',
            'message_id' => $messageId,
            'sent_at' => now(),
        ]);
    }

    public function markAsOpened(): void
    {
        if ($this->status === 'sent') {
            $this->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }
    }

    public function markAsClicked(): void
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);

        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function markAsBounced(): void
    {
        $this->update(['status' => 'bounced']);
    }

    public function markAsComplained(): void
    {
        $this->update(['status' => 'complained']);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }
}
