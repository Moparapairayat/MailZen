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
        Schema::create('delivery_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['smtp', 'sendmail', 'amazon-ses', 'mailgun', 'sendgrid', 'postmark', 'sparkpost'])->default('smtp');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->string('hostname')->nullable();
            $table->integer('port')->default(587);
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->enum('encryption', ['ssl', 'tls', 'none'])->default('tls');
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('reply_to_email')->nullable();
            $table->integer('timeout')->default(30);
            $table->integer('max_connection_messages')->default(100);
            $table->integer('hourly_quota')->default(0);
            $table->integer('daily_quota')->default(0);
            $table->integer('monthly_quota')->default(0);
            $table->integer('pause_after_send')->default(0);
            $table->json('settings')->nullable();
            $table->boolean('locked')->default(false);
            $table->boolean('use_for')->default(true); // Use for campaigns
            $table->boolean('use_for_email_to_list')->default(false);
            $table->boolean('use_for_transactional')->default(false);
            $table->integer('bounce_server_id')->nullable();
            $table->integer('tracking_domain_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_servers');
    }
};

