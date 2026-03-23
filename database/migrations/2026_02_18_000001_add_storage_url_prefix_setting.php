<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'storage_url_prefix'],
            [
                'category' => 'storage',
                'value' => '',
                'type' => 'string',
                'description' => 'Optional prefix added before /storage in public file URLs (e.g. "public" -> /public/storage/...).',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'storage_url_prefix')->delete();
    }
};
