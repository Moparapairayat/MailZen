<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class ListSubscriber extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $fillable = [
        'list_id',
        'email',
        'first_name',
        'last_name',
        'status',
        'source',
        'ip_address',
        'subscribed_at',
        'confirmed_at',
        'unsubscribed_at',
        'blacklisted_at',
        'bounced_at',
        'is_bounced',
        'is_complained',
        'soft_bounce_count',
        'suppressed_at',
        'custom_fields',
        'tags',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'tags' => 'array',
            'subscribed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'blacklisted_at' => 'datetime',
            'bounced_at' => 'datetime',
            'suppressed_at' => 'datetime',
            'is_bounced' => 'boolean',
            'is_complained' => 'boolean',
            'soft_bounce_count' => 'integer',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function campaignTracking(): HasMany
    {
        return $this->hasMany(CampaignTracking::class, 'subscriber_id');
    }

    public function emailVerifications(): HasMany
    {
        return $this->hasMany(EmailVerification::class, 'subscriber_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: $this->email;
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isUnsubscribed(): bool
    {
        return $this->status === 'unsubscribed';
    }

    public function isBlacklisted(): bool
    {
        return $this->status === 'blacklisted';
    }

    public function isBounced(): bool
    {
        return $this->is_bounced || $this->status === 'bounced';
    }

    public function isComplained(): bool
    {
        return $this->is_complained;
    }

    public function isSuppressed(): bool
    {
        return $this->is_bounced || $this->is_complained || $this->suppressed_at !== null;
    }

    public function bounceLogs(): HasMany
    {
        return $this->hasMany(BounceLog::class, 'subscriber_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'subscriber_id');
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail($notification)
    {
        return $this->email;
    }
}
