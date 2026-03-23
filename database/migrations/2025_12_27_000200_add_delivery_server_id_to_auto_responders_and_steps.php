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
        Schema::table('auto_responders', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_responders', 'delivery_server_id')) {
                $table->foreignId('delivery_server_id')->nullable()->after('list_id')->constrained('delivery_servers')->onDelete('set null');
                $table->index('delivery_server_id');
            }
        });

        Schema::table('auto_responder_steps', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_responder_steps', 'delivery_server_id')) {
                $table->foreignId('delivery_server_id')->nullable()->after('template_id')->constrained('delivery_servers')->onDelete('set null');
                $table->index('delivery_server_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_responder_steps', function (Blueprint $table) {
            if (Schema::hasColumn('auto_responder_steps', 'delivery_server_id')) {
                $table->dropForeign(['delivery_server_id']);
                $table->dropIndex(['delivery_server_id']);
                $table->dropColumn('delivery_server_id');
            }
        });

        Schema::table('auto_responders', function (Blueprint $table) {
            if (Schema::hasColumn('auto_responders', 'delivery_server_id')) {
                $table->dropForeign(['delivery_server_id']);
                $table->dropIndex(['delivery_server_id']);
                $table->dropColumn('delivery_server_id');
            }
        });
    }
};
