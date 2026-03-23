<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_id')->constrained('automations')->onDelete('cascade');
            $table->foreignId('subscriber_id')->constrained('list_subscribers')->onDelete('cascade');

            $table->enum('status', ['active', 'completed', 'stopped'])->default('active');
            $table->string('trigger_event');
            $table->json('trigger_context')->nullable();

            $table->string('current_node_id')->nullable();
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('next_scheduled_for')->nullable();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->index(['automation_id', 'subscriber_id']);
            $table->index(['status', 'next_scheduled_for']);
            $table->index('locked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
    }
};
