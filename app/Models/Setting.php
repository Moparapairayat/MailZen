<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    use HasFactory;

    protected static ?bool $settingsTableExists = null;

    protected static function canQuerySettingsTable(): bool
    {
        if (static::$settingsTableExists !== null) {
            return static::$settingsTableExists;
        }

        try {
            static::$settingsTableExists = Schema::hasTable('settings');
        } catch (\Throwable $e) {
            static::$settingsTableExists = false;
        }

        return static::$settingsTableExists;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'key',
        'value',
        'type',
        'description',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /**
     * Get setting value with proper type casting.
     */
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Set setting value with proper type handling.
     */
    public function setValueAttribute($value): void
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Get a setting by key.
     */
    public static function get(string $key, $default = null)
    {
        if (!static::canQuerySettingsTable()) {
            return $default;
        }

        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value.
     */
    public static function set(string $key, $value, string $category = 'general', string $type = 'string'): self
    {
        if (!static::canQuerySettingsTable()) {
            $setting = new static();
            $setting->category = $category;
            $setting->key = $key;
            $setting->value = $value;
            $setting->type = $type;
            return $setting;
        }

        if (in_array($type, ['json', 'array'], true) && is_array($value)) {
            $value = json_encode($value);
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'category' => $category,
                'value' => $value,
                'type' => $type,
            ]
        );
    }

    /**
     * Get all settings by category.
     */
    public static function getByCategory(string $category): \Illuminate\Support\Collection
    {
        if (!static::canQuerySettingsTable()) {
            return collect();
        }

        return static::where('category', $category)->get()->pluck('value', 'key');
    }
}

