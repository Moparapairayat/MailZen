<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->string('destination_url')->nullable()->after('status');
            $table->decimal('commission_rate_percent', 5, 2)->nullable()->after('destination_url');
        });
    }

    public function down(): void
    {
        Schema::table('affiliates', function (Blueprint $table) {
            $table->dropColumn(['destination_url', 'commission_rate_percent']);
        });
    }
};
