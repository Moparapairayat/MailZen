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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email'); // Complained email address
            $table->enum('source', ['webhook', 'email', 'manual'])->default('webhook');
            $table->string('provider')->nullable(); // mailgun, ses, sendgrid, etc.
            $table->string('provider_message_id')->nullable(); // Provider's message ID
            $table->string('feedback_id')->nullable(); // Provider's feedback/complaint ID
            $table->timestamp('complained_at');
            $table->text('raw_data')->nullable(); // Raw webhook/email data for debugging
            $table->json('meta')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index('subscriber_id');
            $table->index('campaign_id');
            $table->index('email');
            $table->index('source');
            $table->index('provider');
            $table->index('complained_at');
            $table->unique(['email', 'provider_message_id']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
