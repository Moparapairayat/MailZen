<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('plan_id');
            $table->string('plan_name');
            $table->enum('status', ['active', 'cancelled', 'expired', 'pending', 'suspended'])->default('pending');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('features')->nullable(); // Plan features
            $table->json('limits')->nullable(); // Plan limits
            $table->string('payment_method')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('payment_reference')->nullable();
            $table->boolean('auto_renew')->default(true);
            $table->integer('renewal_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
