<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'currency',
        'billing_cycle',
        'trial_days',
        'features',
        'limits',
        'customer_group_id',
        'stripe_price_id',
        'stripe_product_id',
        'cta_text',
        'is_active',
        'is_popular',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'trial_days' => 'integer',
            'price' => 'decimal:2',
            'features' => 'array',
            'limits' => 'array',
        ];
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}

