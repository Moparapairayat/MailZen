<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliatePayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'affiliate_id',
        'amount',
        'currency',
        'status',
        'notes',
        'payout_details_snapshot',
        'requested_at',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payout_details_snapshot' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(AffiliateCommission::class, 'payout_id');
    }
}
