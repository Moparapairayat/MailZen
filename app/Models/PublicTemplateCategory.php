<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PublicTemplateCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);

                $originalSlug = $category->slug;
                $count = 1;
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function publicTemplates(): HasMany
    {
        return $this->hasMany(PublicTemplate::class, 'category_id');
    }
}
