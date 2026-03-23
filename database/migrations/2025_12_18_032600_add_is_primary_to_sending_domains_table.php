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
        Schema::table('sending_domains', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('domain');
            $table->index(['customer_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sending_domains', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'is_primary']);
            $table->dropColumn('is_primary');
        });
    }
};
