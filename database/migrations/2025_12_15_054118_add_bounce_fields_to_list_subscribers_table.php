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
        Schema::table('list_subscribers', function (Blueprint $table) {
            $table->boolean('is_bounced')->default(false)->after('status');
            $table->boolean('is_complained')->default(false)->after('is_bounced');
            $table->integer('soft_bounce_count')->default(0)->after('is_complained');
            $table->timestamp('suppressed_at')->nullable()->after('bounced_at');
            
            $table->index('is_bounced');
            $table->index('is_complained');
            $table->index('suppressed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_subscribers', function (Blueprint $table) {
            $table->dropIndex(['is_bounced']);
            $table->dropIndex(['is_complained']);
            $table->dropIndex(['suppressed_at']);
            $table->dropColumn(['is_bounced', 'is_complained', 'soft_bounce_count', 'suppressed_at']);
        });
    }
};
