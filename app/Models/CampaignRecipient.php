<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampaignRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'email',
        'uuid',
        'first_name',
        'last_name',
        'status',
        'sent_at',
        'opened_at',
        'clicked_at',
        'replied_at',
        'bounced_at',
        'failed_at',
        'failure_reason',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
            'clicked_at' => 'datetime',
            'replied_at' => 'datetime',
            'bounced_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($recipient) {
            if (empty($recipient->uuid)) {
                $recipient->uuid = (string) Str::uuid();
            }
        });
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'recipient_id');
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsOpened(): void
    {
        if ($this->opened_at) {
            return;
        }

        $data = [
            'opened_at' => now(),
        ];

        if ($this->status !== 'clicked') {
            $data['status'] = 'opened';
        }

        $this->update($data);
    }

    public function markAsClicked(): void
    {
        $openedAt = $this->opened_at ?? now();
        $clickedAt = $this->clicked_at ?? now();

        $this->update([
            'status' => 'clicked',
            'opened_at' => $openedAt,
            'clicked_at' => $clickedAt,
        ]);
    }

    public function markAsReplied(): void
    {
        if ($this->replied_at) {
            return;
        }

        $this->update([
            'replied_at' => now(),
        ]);
    }

    public function markAsBounced(): void
    {
        $this->update([
            'status' => 'bounced',
            'bounced_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isOpened(): bool
    {
        return $this->opened_at !== null;
    }

    public function isClicked(): bool
    {
        return $this->status === 'clicked';
    }

    public function isReplied(): bool
    {
        return $this->replied_at !== null;
    }
}
