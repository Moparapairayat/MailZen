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
        Schema::table('delivery_servers', function (Blueprint $table) {
            $table->boolean('is_primary')->default(false)->after('name');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_servers', function (Blueprint $table) {
            $table->dropIndex(['is_primary']);
            $table->dropColumn('is_primary');
        });
    }
};
