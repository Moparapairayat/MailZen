<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'admin_user_id',
        'tool',
        'provider',
        'model',
        'used_admin_keys',
        'prompt',
        'input',
        'success',
        'output',
        'tokens_used',
        'error_message',
    ];

    protected $casts = [
        'used_admin_keys' => 'boolean',
        'input' => 'array',
        'success' => 'boolean',
        'tokens_used' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
