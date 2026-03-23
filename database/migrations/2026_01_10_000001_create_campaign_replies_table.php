<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
            $table->foreignId('recipient_id')->nullable()->constrained('campaign_recipients')->nullOnDelete();

            $table->string('message_id')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('subject')->nullable();
            $table->longText('body_text')->nullable();
            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            $table->index(['campaign_id', 'received_at']);
            $table->index(['campaign_id', 'recipient_id']);
            $table->unique(['campaign_id', 'message_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_replies');
    }
};
