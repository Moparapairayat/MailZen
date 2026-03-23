<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingDomain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'domain',
        'status',
        'verification_token',
        'verified_at',
        'dns_records',
        'verification_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dns_records' => 'array',
            'verification_data' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'tracking_domain_id');
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified' && $this->verified_at !== null;
    }
}
