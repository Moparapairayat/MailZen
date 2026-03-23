<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->unsignedBigInteger('referred_customer_id');
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('manual_payment_id')->nullable();

            $table->string('provider', 32)->nullable();
            $table->string('event_key', 190)->unique();

            $table->decimal('base_amount', 12, 2)->default(0);
            $table->string('base_currency', 10)->nullable();

            $table->string('commission_type', 16)->default('percent');
            $table->decimal('commission_rate', 12, 4)->nullable();
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->string('commission_currency', 10)->nullable();

            $table->string('status', 32)->default('pending');
            $table->unsignedBigInteger('payout_id')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
            $table->index(['referred_customer_id']);
            $table->index(['subscription_id']);

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            $table->foreign('referred_customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
            $table->foreign('manual_payment_id')->references('id')->on('manual_payments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
