<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('openai_api_key')->nullable();
            $table->text('gemini_api_key')->nullable();

            $table->unsignedBigInteger('ai_token_usage')->default(0);
            $table->unsignedInteger('ai_image_credits_used')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'openai_api_key',
                'gemini_api_key',
                'ai_token_usage',
                'ai_image_credits_used',
            ]);
        });
    }
};
