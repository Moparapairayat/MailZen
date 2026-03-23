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
        Schema::create('auto_responder_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_responder_id')->constrained('auto_responders')->onDelete('cascade');
            $table->foreignId('subscriber_id')->constrained('list_subscribers')->onDelete('cascade');
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');

            $table->enum('status', ['active', 'completed', 'stopped'])->default('active');
            $table->timestamp('triggered_at')->nullable();

            $table->unsignedInteger('next_step_order')->default(1);
            $table->timestamp('next_scheduled_for')->nullable();

            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->string('stop_reason')->nullable();

            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->unique(['auto_responder_id', 'subscriber_id']);
            $table->index(['list_id', 'status']);
            $table->index('next_scheduled_for');
            $table->index('locked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responder_runs');
    }
};
