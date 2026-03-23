<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'percent_off',
        'amount_off',
        'currency',
        'duration',
        'duration_in_months',
        'max_redemptions',
        'redeemed_count',
        'starts_at',
        'ends_at',
        'is_active',
        'stripe_coupon_id',
        'stripe_promotion_code_id',
    ];

    protected function casts(): array
    {
        return [
            'percent_off' => 'decimal:2',
            'amount_off' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isUsable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->max_redemptions !== null && $this->redeemed_count >= $this->max_redemptions) {
            return false;
        }

        return true;
    }
}
