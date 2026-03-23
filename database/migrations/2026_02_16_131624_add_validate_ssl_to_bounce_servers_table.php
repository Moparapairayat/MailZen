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
        Schema::table('bounce_servers', function (Blueprint $table) {
            $table->boolean('validate_ssl')->default(true)->after('max_emails_per_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bounce_servers', function (Blueprint $table) {
            $table->dropColumn('validate_ssl');
        });
    }
};
