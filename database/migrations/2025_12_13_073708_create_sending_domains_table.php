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
        Schema::create('sending_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('domain');
            $table->enum('status', ['active', 'inactive', 'pending', 'verified', 'failed'])->default('pending');
            $table->text('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('spf_record')->nullable();
            $table->text('dkim_public_key')->nullable();
            $table->text('dkim_private_key')->nullable();
            $table->text('dmarc_record')->nullable();
            $table->text('dns_records')->nullable(); // JSON array of required DNS records
            $table->json('verification_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('domain');
            $table->index('status');
            $table->unique('domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sending_domains');
    }
};
