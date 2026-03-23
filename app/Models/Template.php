<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'slug',
        'description',
        'type',
        'html_content',
        'plain_text_content',
        'grapesjs_data',
        'settings',
        'thumbnail',
        'is_public',
        'is_system',
        'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'grapesjs_data' => 'array',
            'settings' => 'array',
            'is_public' => 'boolean',
            'is_system' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
                
                // Ensure unique slug
                $originalSlug = $template->slug;
                $count = 1;
                while (static::where('slug', $template->slug)->exists()) {
                    $template->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'template_id');
    }

    public function autoResponders(): HasMany
    {
        return $this->hasMany(AutoResponder::class, 'template_id');
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'template_customer_group', 'template_id', 'customer_group_id')
            ->withTimestamps();
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function isPublic(): bool
    {
        return $this->is_public;
    }

    public function isSystem(): bool
    {
        return $this->is_system;
    }
}
