<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('service');
            $table->string('google_account_email')->nullable();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('scopes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'service']);
            $table->index(['service']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_integrations');
    }
};
