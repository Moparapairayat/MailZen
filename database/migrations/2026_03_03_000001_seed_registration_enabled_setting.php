<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'registration_enabled'],
            [
                'category' => 'auth',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable public customer registration.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'registration_enabled')->delete();
    }
};
