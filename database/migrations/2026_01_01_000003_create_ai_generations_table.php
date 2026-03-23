<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('tool')->index();
            $table->string('provider')->index();
            $table->boolean('used_admin_keys')->default(false);

            $table->longText('prompt');
            $table->json('input')->nullable();

            $table->boolean('success')->default(false);
            $table->longText('output')->nullable();
            $table->unsignedInteger('tokens_used')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
