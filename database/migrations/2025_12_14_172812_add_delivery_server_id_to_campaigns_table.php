<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('delivery_server_id')->nullable()->after('tracking_domain_id')->constrained('delivery_servers')->onDelete('set null');
            $table->index('delivery_server_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['delivery_server_id']);
            $table->dropIndex(['delivery_server_id']);
            $table->dropColumn('delivery_server_id');
        });
    }
};
