<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BuiltInTemplateSetting extends Model
{
    protected $fillable = [
        'builder',
        'template_key',
        'relative_path',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'built_in_template_customer_group', 'built_in_template_setting_id', 'customer_group_id')
            ->withTimestamps();
    }
}
