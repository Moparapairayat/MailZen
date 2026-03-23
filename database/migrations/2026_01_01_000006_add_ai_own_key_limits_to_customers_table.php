<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('ai_own_daily_limit')->default(0);
            $table->unsignedBigInteger('ai_own_monthly_limit')->default(0);

            $table->unsignedBigInteger('ai_own_daily_usage')->default(0);
            $table->date('ai_own_daily_usage_date')->nullable();

            $table->unsignedBigInteger('ai_own_monthly_usage')->default(0);
            $table->string('ai_own_monthly_usage_month', 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'ai_own_daily_limit',
                'ai_own_monthly_limit',
                'ai_own_daily_usage',
                'ai_own_daily_usage_date',
                'ai_own_monthly_usage',
                'ai_own_monthly_usage_month',
            ]);
        });
    }
};
