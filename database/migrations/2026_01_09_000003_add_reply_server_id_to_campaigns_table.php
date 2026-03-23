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
            if (!Schema::hasColumn('campaigns', 'reply_server_id')) {
                $table->foreignId('reply_server_id')->nullable()->after('delivery_server_id')->constrained('reply_servers')->nullOnDelete();
                $table->index('reply_server_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'reply_server_id')) {
                $table->dropConstrainedForeignId('reply_server_id');
            }
        });
    }
};
