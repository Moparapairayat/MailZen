<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');
            $table->string('visitor_id', 64)->nullable();

            $table->unsignedBigInteger('referred_customer_id')->nullable();
            $table->timestamp('referred_at')->nullable();

            $table->string('landing_url')->nullable();
            $table->string('referrer_url')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['affiliate_id', 'created_at']);
            $table->index(['referred_customer_id']);

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
            $table->foreign('referred_customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_referrals');
    }
};
