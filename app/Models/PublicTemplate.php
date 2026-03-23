<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PublicTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'created_by_admin_user_id',
        'name',
        'slug',
        'description',
        'type',
        'builder',
        'html_content',
        'plain_text_content',
        'builder_data',
        'settings',
        'thumbnail',
        'is_active',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'builder_data' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);

                $originalSlug = $template->slug;
                $count = 1;
                while (static::where('slug', $template->slug)->exists()) {
                    $template->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PublicTemplateCategory::class, 'category_id');
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'public_template_customer_group', 'public_template_id', 'customer_group_id')
            ->withTimestamps();
    }

    public function createdByAdminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_user_id');
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
