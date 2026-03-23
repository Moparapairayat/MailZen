<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerLoginEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'ip_geolocation_id',
        'ip',
        'user_agent',
        'logged_in_at',
    ];

    protected function casts(): array
    {
        return [
            'logged_in_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function ipGeolocation(): BelongsTo
    {
        return $this->belongsTo(IpGeolocation::class);
    }
}
