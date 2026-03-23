<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'referred_customer_id',
        'subscription_id',
        'manual_payment_id',
        'provider',
        'event_key',
        'base_amount',
        'base_currency',
        'commission_type',
        'commission_rate',
        'commission_amount',
        'commission_currency',
        'status',
        'payout_id',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'base_amount' => 'decimal:2',
            'commission_rate' => 'decimal:4',
            'commission_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function referredCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    public function manualPayment(): BelongsTo
    {
        return $this->belongsTo(ManualPayment::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayout::class, 'payout_id');
    }
}
