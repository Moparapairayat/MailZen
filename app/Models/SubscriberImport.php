<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'list_id',
        'status',
        'source',
        'ip_address',
        'stored_path',
        'headers',
        'column_mapping',
        'skip_duplicates',
        'update_existing',
        'file_offset',
        'locked_at',
        'total_rows',
        'processed_count',
        'imported_count',
        'updated_count',
        'skipped_count',
        'error_count',
        'failure_reason',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'column_mapping' => 'array',
            'skip_duplicates' => 'boolean',
            'update_existing' => 'boolean',
            'file_offset' => 'integer',
            'locked_at' => 'datetime',
            'total_rows' => 'integer',
            'processed_count' => 'integer',
            'imported_count' => 'integer',
            'updated_count' => 'integer',
            'skipped_count' => 'integer',
            'error_count' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
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
}
