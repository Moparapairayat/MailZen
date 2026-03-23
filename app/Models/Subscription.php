<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'plan_id',
        'plan_db_id',
        'plan_name',
        'status',
        'billing_cycle',
        'price',
        'currency',
        'starts_at',
        'period_start',
        'period_end',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'cancellation_reason',
        'features',
        'limits',
        'payment_method',
        'payment_gateway',
        'provider',
        'stripe_customer_id',
        'stripe_subscription_id',
        'stripe_checkout_session_id',
        'stripe_price_id',
        'payment_reference',
        'last_payment_status',
        'auto_renew',
        'cancel_at_period_end',
        'renewal_count',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'limits' => 'array',
            'starts_at' => 'datetime',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'auto_renew' => 'boolean',
            'cancel_at_period_end' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_db_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }
}
