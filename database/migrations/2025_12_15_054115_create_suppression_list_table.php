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
        Schema::create('suppression_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email');
            $table->enum('reason', ['bounce', 'complaint', 'manual', 'unsubscribe'])->default('manual');
            $table->text('reason_description')->nullable();
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('suppressed_at');
            $table->timestamps();

            $table->index('customer_id');
            $table->index('email');
            $table->index('reason');
            $table->index('suppressed_at');
            $table->unique(['customer_id', 'email']); // One suppression per email per customer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppression_list');
    }
};
