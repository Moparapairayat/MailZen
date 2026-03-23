<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailValidationRunItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_id',
        'subscriber_id',
        'email',
        'success',
        'result',
        'message',
        'action_taken',
        'flags',
        'raw',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'flags' => 'array',
            'raw' => 'array',
            'validated_at' => 'datetime',
        ];
    }

    public function run(): BelongsTo
    {
        return $this->belongsTo(EmailValidationRun::class, 'run_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }
}
