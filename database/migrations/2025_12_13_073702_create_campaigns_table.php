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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('list_id')->nullable()->constrained('email_lists')->onDelete('set null');
            $table->string('name');
            $table->string('subject');
            $table->text('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->enum('type', ['regular', 'autoresponder', 'recurring'])->default('regular');
            $table->enum('status', ['draft', 'queued', 'scheduled', 'running', 'paused', 'completed', 'failed'])->default('draft');
            $table->longText('html_content')->nullable();
            $table->longText('plain_text_content')->nullable();
            $table->json('template_data')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('send_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->integer('complained_count')->default(0);
            $table->decimal('open_rate', 5, 2)->default(0);
            $table->decimal('click_rate', 5, 2)->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);
            $table->foreignId('tracking_domain_id')->nullable();
            $table->json('segments')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('list_id');
            $table->index('status');
            $table->index('type');
            $table->index('scheduled_at');
            $table->index('send_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
