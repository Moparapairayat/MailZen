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
        Schema::create('campaign_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('cascade');
            $table->string('name'); // Variant A, Variant B, etc.
            $table->string('subject')->nullable(); // Different subject line
            $table->text('html_content')->nullable(); // Different HTML content
            $table->text('plain_text_content')->nullable(); // Different plain text content
            $table->integer('split_percentage')->default(50); // Percentage of audience for this variant
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->decimal('open_rate', 5, 2)->default(0);
            $table->decimal('click_rate', 5, 2)->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->boolean('is_winner')->default(false); // Mark winning variant
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('is_winner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_variants');
    }
};
