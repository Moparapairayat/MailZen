<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('status', 16)->default('draft')->after('featured_image');
            $table->timestamp('scheduled_at')->nullable()->after('status');

            $table->index(['status', 'scheduled_at']);
        });

        DB::table('blog_posts')->where('is_published', true)->update(['status' => 'publish']);
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropIndex(['status', 'scheduled_at']);
            $table->dropColumn(['status', 'scheduled_at']);
        });
    }
};
