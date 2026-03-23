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
        Schema::create('email_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->enum('opt_in', ['single', 'double'])->default('double');
            $table->enum('opt_out', ['single', 'double'])->default('single');
            $table->boolean('welcome_email_enabled')->default(true);
            $table->text('welcome_email_subject')->nullable();
            $table->longText('welcome_email_content')->nullable();
            $table->boolean('unsubscribe_email_enabled')->default(true);
            $table->text('unsubscribe_email_subject')->nullable();
            $table->longText('unsubscribe_email_content')->nullable();
            $table->json('custom_fields')->nullable();
            $table->json('tags')->nullable();
            $table->integer('subscribers_count')->default(0);
            $table->integer('confirmed_subscribers_count')->default(0);
            $table->integer('unsubscribed_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->timestamp('last_subscriber_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('status');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_lists');
    }
};
