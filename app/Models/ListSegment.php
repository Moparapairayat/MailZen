<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ListSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'list_id',
        'name',
        'description',
        'rules',
        'subscribers_count',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function subscribers(): BelongsToMany
    {
        // This would be a dynamic relationship based on rules
        // For now, we'll implement it as a method that queries based on rules
        return $this->belongsToMany(ListSubscriber::class, 'list_segment_subscriber', 'segment_id', 'subscriber_id');
    }
}

