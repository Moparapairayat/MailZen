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
        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->constrained('campaign_recipients')->onDelete('cascade');
            $table->string('event'); // sent, opened, clicked, bounced, failed
            $table->json('meta')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('url')->nullable(); // For click events
            $table->text('error_message')->nullable(); // For failed events
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('recipient_id');
            $table->index('event');
            $table->index('created_at');
            $table->index(['campaign_id', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_logs');
    }
};

