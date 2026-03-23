<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_provider_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('event_type');

            $table->string('sns_message_id')->nullable();
            $table->string('ses_message_id')->nullable();
            $table->string('topic_arn')->nullable();

            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('recipient_id')->nullable()->constrained('campaign_recipients')->onDelete('set null');
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('set null');

            $table->string('email')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['provider', 'event_type']);
            $table->index('sns_message_id');
            $table->index('ses_message_id');
            $table->index('topic_arn');
            $table->index('email');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_provider_events');
    }
};
