<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryServer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'is_primary',
        'type',
        'status',
        'hostname',
        'port',
        'username',
        'password',
        'encryption',
        'from_email',
        'from_name',
        'reply_to_email',
        'timeout',
        'max_connection_messages',
        'second_quota',
        'minute_quota',
        'hourly_quota',
        'daily_quota',
        'monthly_quota',
        'pause_after_send',
        'settings',
        'locked',
        'use_for',
        'use_for_email_to_list',
        'use_for_transactional',
        'bounce_server_id',
        'tracking_domain_id',
        'notes',
        'verification_token',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'settings' => 'array',
            'locked' => 'boolean',
            'use_for' => 'boolean',
            'use_for_email_to_list' => 'boolean',
            'use_for_transactional' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function trackingDomain(): BelongsTo
    {
        return $this->belongsTo(TrackingDomain::class, 'tracking_domain_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bounceServer(): BelongsTo
    {
        return $this->belongsTo(BounceServer::class, 'bounce_server_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * Check if delivery server is verified.
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Generate and set verification token.
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update(['verification_token' => $token]);
        return $token;
    }

    /**
     * Mark delivery server as verified.
     */
    public function markAsVerified(): void
    {
        $this->update([
            'verified_at' => now(),
            'verification_token' => null,
        ]);
    }
}

