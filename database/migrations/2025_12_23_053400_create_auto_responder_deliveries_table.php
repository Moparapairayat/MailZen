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
        Schema::create('auto_responder_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_responder_id')->constrained('auto_responders')->onDelete('cascade');
            $table->foreignId('subscriber_id')->constrained('list_subscribers')->onDelete('cascade');
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');
            $table->enum('status', ['pending', 'sent', 'skipped', 'failed'])->default('pending');
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->unique(['auto_responder_id', 'subscriber_id']);
            $table->index(['list_id', 'status']);
            $table->index('scheduled_for');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responder_deliveries');
    }
};
