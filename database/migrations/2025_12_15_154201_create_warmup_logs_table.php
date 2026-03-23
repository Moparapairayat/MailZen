<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warmup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_warmup_id')->constrained()->cascadeOnDelete();
            
            $table->date('send_date');
            $table->integer('day_number');
            $table->integer('target_volume');
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('complained_count')->default(0);
            
            $table->decimal('open_rate', 5, 2)->nullable();
            $table->decimal('click_rate', 5, 2)->nullable();
            $table->decimal('bounce_rate', 5, 2)->nullable();
            
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->unique(['email_warmup_id', 'send_date']);
            $table->index(['email_warmup_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warmup_logs');
    }
};
