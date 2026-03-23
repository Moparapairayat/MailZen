<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_warmups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('delivery_server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('email_list_id')->nullable()->constrained('email_lists')->nullOnDelete();
            
            $table->string('name');
            $table->string('from_email');
            $table->string('from_name')->nullable();
            
            $table->enum('status', ['draft', 'active', 'paused', 'completed', 'failed'])->default('draft');
            
            $table->integer('starting_volume')->default(10);
            $table->integer('max_volume')->default(500);
            $table->decimal('daily_increase_rate', 5, 2)->default(1.20);
            $table->integer('current_day')->default(0);
            $table->integer('total_days')->default(30);
            
            $table->time('send_time')->default('09:00:00');
            $table->string('timezone')->default('UTC');
            
            $table->integer('total_sent')->default(0);
            $table->integer('total_opened')->default(0);
            $table->integer('total_clicked')->default(0);
            $table->integer('total_bounced')->default(0);
            $table->integer('total_complained')->default(0);
            
            $table->json('email_templates')->nullable();
            $table->json('settings')->nullable();
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['customer_id', 'status']);
            $table->index(['delivery_server_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_warmups');
    }
};
