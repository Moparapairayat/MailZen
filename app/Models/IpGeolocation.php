<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpGeolocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'country',
        'country_code',
        'region',
        'city',
        'latitude',
        'longitude',
        'timezone',
        'org',
        'raw',
        'looked_up_at',
    ];

    protected function casts(): array
    {
        return [
            'raw' => 'array',
            'looked_up_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }
}
