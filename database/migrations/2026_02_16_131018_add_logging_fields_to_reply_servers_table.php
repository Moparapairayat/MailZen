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
        Schema::table('reply_servers', function (Blueprint $table) {
            $table->json('process_logs')->nullable()->after('validate_ssl');
            $table->json('error_logs')->nullable()->after('process_logs');
            $table->timestamp('last_processed_at')->nullable()->after('error_logs');
            $table->timestamp('last_error_at')->nullable()->after('last_processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reply_servers', function (Blueprint $table) {
            $table->dropColumn(['process_logs', 'error_logs', 'last_processed_at', 'last_error_at']);
        });
    }
};
