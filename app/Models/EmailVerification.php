<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'email',
        'token',
        'type',
        'status',
        'list_id',
        'subscriber_id',
        'verified_at',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified' && $this->verified_at !== null;
    }
}
