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
        Schema::create('campaign_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('set null');
            $table->string('email');
            $table->enum('event_type', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'unsubscribed', 'complained'])->default('sent');
            $table->string('url')->nullable(); // For click tracking
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('bounce_reason')->nullable();
            $table->text('complaint_reason')->nullable();
            $table->timestamp('event_at');
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->index('email');
            $table->index('event_type');
            $table->index('event_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_tracking');
    }
};
