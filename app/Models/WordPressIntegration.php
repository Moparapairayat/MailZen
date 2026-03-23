<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordPressIntegration extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'signing_secret',
        'meta',
        'last_rotated_at',
    ];

    protected function casts(): array
    {
        return [
            'signing_secret' => 'encrypted',
            'meta' => 'array',
            'last_rotated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
