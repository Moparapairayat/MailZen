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
            // Drop the old string column if it exists
            $table->dropColumn('tracking_domain_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            // Add the foreign key column
            $table->foreignId('tracking_domain_id')->nullable()->after('track_clicks')->constrained('tracking_domains')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['tracking_domain_id']);
            $table->dropColumn('tracking_domain_id');
        });

        Schema::table('campaigns', function (Blueprint $table) {
            $table->string('tracking_domain_id')->nullable()->after('track_clicks');
        });
    }
};
