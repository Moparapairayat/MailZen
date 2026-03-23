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
            if (!Schema::hasColumn('campaigns', 'replied_count')) {
                $table->integer('replied_count')->default(0)->after('complained_count');
            }
        });

        Schema::table('campaign_recipients', function (Blueprint $table) {
            if (!Schema::hasColumn('campaign_recipients', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('clicked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_recipients', function (Blueprint $table) {
            if (Schema::hasColumn('campaign_recipients', 'replied_at')) {
                $table->dropColumn('replied_at');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'replied_count')) {
                $table->dropColumn('replied_count');
            }
        });
    }
};
