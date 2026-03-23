<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SubscriptionForm extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'list_id',
        'name',
        'title',
        'type',
        'builder',
        'slug',
        'description',
        'html_content',
        'plain_text_content',
        'builder_data',
        'fields',
        'settings',
        'gdpr_checkbox',
        'gdpr_text',
        'is_active',
        'submissions_count',
    ];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'settings' => 'array',
            'builder_data' => 'array',
            'gdpr_checkbox' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->name) . '-' . Str::random(8);
            }
        });
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }
}

