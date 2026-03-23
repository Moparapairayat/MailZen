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
        Schema::create('bounced_emails', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bounce_server_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('delivery_server_id')->nullable()->constrained('delivery_servers')->onDelete('set null');

            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('list_id')->nullable()->constrained('email_lists')->onDelete('set null');
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('set null');
            $table->foreignId('recipient_id')->nullable()->constrained('campaign_recipients')->onDelete('set null');

            $table->string('email');

            $table->string('bounce_server_username')->nullable();
            $table->string('bounce_server_mailbox')->nullable();

            $table->enum('bounce_type', ['hard', 'soft', 'unknown'])->default('unknown');
            $table->string('bounce_code')->nullable();
            $table->text('diagnostic_code')->nullable();
            $table->text('reason')->nullable();

            $table->longText('raw_message')->nullable();
            $table->timestamp('last_bounced_at')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index('bounce_server_id');
            $table->index('delivery_server_id');
            $table->index('campaign_id');
            $table->index('list_id');
            $table->index('subscriber_id');
            $table->index('recipient_id');
            $table->index('email');
            $table->index('bounce_type');
            $table->index('last_bounced_at');
            $table->unique(['campaign_id', 'list_id', 'email'], 'bounced_emails_campaign_list_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bounced_emails');
    }
};
