<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('event_id', 191)->unique();
            $table->string('type', 191);
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['provider', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};

