<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service',
        'google_account_email',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'scopes' => 'array',
            'meta' => 'array',
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
