<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::whereIn('key', [
            'auth_brand_color',
            'auth_gradient_from',
            'auth_gradient_to',
        ])->delete();
    }

    public function down(): void
    {
        // no-op
    }
};
