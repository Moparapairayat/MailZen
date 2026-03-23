<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('plan_id')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 10)->nullable();

            $table->string('status', 32)->default('initiated');
            $table->string('transfer_reference', 190)->nullable();
            $table->text('payer_notes')->nullable();
            $table->string('proof_path')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->unsignedBigInteger('reviewed_by_admin_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->unique(['subscription_id']);

            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
            $table->foreign('reviewed_by_admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_payments');
    }
};
