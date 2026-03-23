<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();

            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('percent_off', 5, 2)->nullable();
            $table->decimal('amount_off', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();

            $table->enum('duration', ['once', 'repeating', 'forever'])->default('once');
            $table->unsignedInteger('duration_in_months')->nullable();

            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redeemed_count')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->string('stripe_coupon_id')->nullable();
            $table->string('stripe_promotion_code_id')->nullable();

            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
