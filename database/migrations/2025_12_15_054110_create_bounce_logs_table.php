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
        Schema::create('bounce_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bounce_server_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email'); // Original recipient email
            $table->enum('bounce_type', ['hard', 'soft', 'unknown'])->default('unknown');
            $table->string('bounce_code')->nullable(); // SMTP bounce code (e.g., 550, 551)
            $table->text('diagnostic_code')->nullable(); // Detailed error message
            $table->text('raw_message')->nullable(); // Full raw bounce email for debugging
            $table->string('message_id')->nullable(); // Original message ID if found
            $table->timestamp('bounced_at')->nullable();
            $table->json('meta')->nullable(); // Additional metadata
            $table->timestamps();

            $table->index('bounce_server_id');
            $table->index('subscriber_id');
            $table->index('campaign_id');
            $table->index('email');
            $table->index('bounce_type');
            $table->index('bounced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounce_logs');
    }
};
