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
            if (!Schema::hasColumn('campaigns', 'bounce_server_id')) {
                $table->foreignId('bounce_server_id')
                    ->nullable()
                    ->after('tracking_domain_id')
                    ->constrained('bounce_servers')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'bounce_server_id')) {
                $table->dropConstrainedForeignId('bounce_server_id');
            }
        });
    }
};
