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
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->enum('type', ['email_verification', 'password_reset', 'list_subscription', 'list_unsubscription'])->default('email_verification');
            $table->enum('status', ['pending', 'verified', 'expired', 'used'])->default('pending');
            $table->foreignId('list_id')->nullable()->constrained('email_lists')->onDelete('cascade');
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('cascade');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index('token');
            $table->index('status');
            $table->index('type');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
