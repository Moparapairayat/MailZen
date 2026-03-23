<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailValidationRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'list_id',
        'tool_id',
        'status',
        'invalid_action',
        'total_emails',
        'processed_count',
        'deliverable_count',
        'undeliverable_count',
        'accept_all_count',
        'unknown_count',
        'error_count',
        'started_at',
        'finished_at',
        'failure_reason',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'total_emails' => 'integer',
            'processed_count' => 'integer',
            'deliverable_count' => 'integer',
            'undeliverable_count' => 'integer',
            'accept_all_count' => 'integer',
            'unknown_count' => 'integer',
            'error_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(EmailValidationTool::class, 'tool_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EmailValidationRunItem::class, 'run_id');
    }
}
