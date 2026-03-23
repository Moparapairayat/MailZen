<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailValidationTool extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'provider',
        'api_key',
        'active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key'] = is_string($value) ? trim($value) : $value;
    }

    public function getApiKeyAttribute($value): string
    {
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(EmailValidationRun::class, 'tool_id');
    }
}
