<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SendingDomain extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'domain',
        'is_primary',
        'status',
        'verification_token',
        'verified_at',
        'spf_record',
        'dkim_public_key',
        'dkim_private_key',
        'dmarc_record',
        'dns_records',
        'verification_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'dns_records' => 'array',
            'verification_data' => 'array',
            'verified_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified' && $this->verified_at !== null;
    }
}
