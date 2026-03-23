<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warmup_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warmup_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_warmup_id')->constrained()->cascadeOnDelete();
            
            $table->string('email');
            $table->string('subject');
            $table->string('message_id')->nullable();
            
            $table->enum('status', ['pending', 'sent', 'opened', 'clicked', 'bounced', 'complained', 'failed'])->default('pending');
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            $table->index(['warmup_log_id', 'status']);
            $table->index(['email_warmup_id', 'status']);
            $table->index('message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warmup_emails');
    }
};
