<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'visitor_id',
        'referred_customer_id',
        'referred_at',
        'landing_url',
        'referrer_url',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'referred_at' => 'datetime',
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
}
