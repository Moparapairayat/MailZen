<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'app_logo_dark'],
            [
                'category' => 'general',
                'value' => null,
                'type' => 'string',
                'description' => 'Dark mode application logo path (stored in public disk).',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'app_logo_dark')->delete();
    }
};
