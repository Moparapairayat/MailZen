<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_forms', function (Blueprint $table) {
            $table->enum('builder', ['basic', 'unlayer'])->default('basic')->after('type');
            $table->longText('html_content')->nullable()->after('description');
            $table->longText('plain_text_content')->nullable()->after('html_content');
            $table->json('builder_data')->nullable()->after('plain_text_content');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_forms', function (Blueprint $table) {
            $table->dropColumn(['builder', 'html_content', 'plain_text_content', 'builder_data']);
        });
    }
};
