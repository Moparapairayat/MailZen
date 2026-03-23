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
        Schema::table('campaign_recipients', function (Blueprint $table) {
            // Add composite index for recent activity queries (campaign_id, status, updated_at)
            $table->index(['campaign_id', 'status', 'updated_at'], 'campaign_recipients_status_updated_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->dropIndex('campaign_recipients_status_updated_index');
        });
    }
};
