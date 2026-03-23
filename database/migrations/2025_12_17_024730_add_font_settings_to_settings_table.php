<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add font settings if they don't exist
        Setting::firstOrCreate(
            ['key' => 'admin_font_family'],
            [
                'category' => 'appearance',
                'value' => 'Inter',
                'type' => 'string',
                'description' => 'Google Font family for admin interface',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'admin_font_weights'],
            [
                'category' => 'appearance',
                'value' => '400,500,600,700',
                'type' => 'string',
                'description' => 'Font weights to load (comma-separated)',
                'is_public' => false,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Setting::whereIn('key', ['admin_font_family', 'admin_font_weights'])->delete();
    }
};
