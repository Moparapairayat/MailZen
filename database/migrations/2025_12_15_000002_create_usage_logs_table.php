<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('metric'); // emails_sent_this_month, subscribers_count, campaigns_count
            $table->unsignedBigInteger('amount')->default(0);
            $table->date('period_start');
            $table->date('period_end');
            $table->json('context')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'metric', 'period_start', 'period_end'], 'usage_unique_period');
            $table->index(['customer_id', 'metric']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};

