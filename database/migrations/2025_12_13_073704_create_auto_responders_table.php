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
        Schema::create('auto_responders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');
            $table->string('name');
            $table->string('subject');
            $table->text('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->enum('trigger', ['subscriber_added', 'subscriber_confirmed', 'subscriber_unsubscribed', 'mail_opened', 'mail_clicked'])->default('subscriber_confirmed');
            $table->json('trigger_settings')->nullable(); // For date_field or custom triggers
            $table->integer('delay_value')->default(0);
            $table->enum('delay_unit', ['minutes', 'hours', 'days', 'weeks'])->default('hours');
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');
            $table->longText('html_content')->nullable();
            $table->longText('plain_text_content')->nullable();
            $table->json('template_data')->nullable();
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('list_id');
            $table->index('status');
            $table->index('trigger');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responders');
    }
};
