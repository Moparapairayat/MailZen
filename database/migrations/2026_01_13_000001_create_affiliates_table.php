<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('code', 64)->unique();
            $table->string('status', 32)->default('pending');

            $table->json('payout_details')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('blocked_at')->nullable();

            $table->timestamps();

            $table->unique(['customer_id']);
            $table->index(['status']);

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
