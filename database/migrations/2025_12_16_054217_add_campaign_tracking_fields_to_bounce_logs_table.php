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
        Schema::table('bounce_logs', function (Blueprint $table) {
            $table->foreignId('list_id')->nullable()->after('campaign_id')->constrained('email_lists')->onDelete('set null');
            $table->foreignId('recipient_id')->nullable()->after('list_id')->constrained('campaign_recipients')->onDelete('set null');
            
            $table->index('list_id');
            $table->index('recipient_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bounce_logs', function (Blueprint $table) {
            $table->dropForeign(['list_id']);
            $table->dropForeign(['recipient_id']);
            $table->dropIndex(['list_id']);
            $table->dropIndex(['recipient_id']);
            $table->dropColumn(['list_id', 'recipient_id']);
        });
    }
};
