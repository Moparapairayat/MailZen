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
        Schema::create('list_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');
            $table->string('email');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->enum('status', ['confirmed', 'unconfirmed', 'unsubscribed', 'blacklisted', 'bounced'])->default('unconfirmed');
            $table->string('source')->nullable(); // web, api, import, etc.
            $table->string('ip_address')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamp('blacklisted_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('list_id');
            $table->index('email');
            $table->index('status');
            $table->unique(['list_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_subscribers');
    }
};
