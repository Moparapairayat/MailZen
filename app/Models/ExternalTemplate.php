<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalTemplate extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'resource_type',
        'template_type',
        'product_id',
        'preview_image',
        'preview_url',
        'requires_license',
        'plan',
        'builder',
        'categories',
        'meta',
        'json_code',
        'json_fetched_at',
        'external_created_at',
        'external_updated_at',
    ];

    protected $casts = [
        'external_id' => 'integer',
        'product_id' => 'integer',
        'requires_license' => 'boolean',
        'categories' => 'array',
        'meta' => 'array',
        'json_fetched_at' => 'datetime',
        'external_created_at' => 'datetime',
        'external_updated_at' => 'datetime',
    ];
}
