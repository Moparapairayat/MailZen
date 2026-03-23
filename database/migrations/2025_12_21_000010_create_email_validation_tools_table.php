<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_validation_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('provider');
            $table->text('api_key');
            $table->boolean('active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('provider');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_validation_tools');
    }
};
