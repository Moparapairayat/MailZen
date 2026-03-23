<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_db_id')->nullable()->after('plan_id')->constrained('plans')->nullOnDelete();
            $table->string('stripe_customer_id')->nullable()->after('payment_reference');
            $table->string('stripe_subscription_id')->nullable()->after('stripe_customer_id');
            $table->string('stripe_checkout_session_id')->nullable()->after('stripe_subscription_id');
            $table->string('stripe_price_id')->nullable()->after('stripe_checkout_session_id');
            $table->string('provider')->default('stripe')->after('payment_gateway');
            $table->enum('status', ['active', 'cancelled', 'expired', 'pending', 'suspended', 'past_due', 'trialing'])->default('pending')->change();
            $table->timestamp('period_start')->nullable()->after('starts_at');
            $table->timestamp('period_end')->nullable()->after('period_start');
            $table->boolean('cancel_at_period_end')->default(false)->after('cancelled_at');
            $table->string('last_payment_status')->nullable()->after('payment_reference');
            $table->index('stripe_subscription_id');
            $table->index('stripe_checkout_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_db_id']);
            $table->dropColumn([
                'plan_db_id',
                'stripe_customer_id',
                'stripe_subscription_id',
                'stripe_checkout_session_id',
                'stripe_price_id',
                'provider',
                'period_start',
                'period_end',
                'cancel_at_period_end',
                'last_payment_status',
            ]);
            $table->enum('status', ['active', 'cancelled', 'expired', 'pending', 'suspended'])->default('pending')->change();
        });
    }
};

