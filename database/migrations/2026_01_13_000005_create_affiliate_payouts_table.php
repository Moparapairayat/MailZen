<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id');

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();

            $table->string('status', 32)->default('requested');
            $table->text('notes')->nullable();

            $table->json('payout_details_snapshot')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['affiliate_id', 'status']);

            $table->foreign('affiliate_id')->references('id')->on('affiliates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_payouts');
    }
};
