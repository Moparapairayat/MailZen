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
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'campaign', 'transactional', 'autoresponder', 'footer', 'signature'])->default('email');
            $table->longText('html_content')->nullable();
            $table->longText('plain_text_content')->nullable();
            $table->json('grapesjs_data')->nullable(); // Store GrapesJS JSON data
            $table->json('settings')->nullable(); // Template settings (width, responsive, etc.)
            $table->string('thumbnail')->nullable(); // Template preview thumbnail
            $table->boolean('is_public')->default(false); // Share with other customers
            $table->boolean('is_system')->default(false); // System templates
            $table->integer('usage_count')->default(0); // How many times used
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('type');
            $table->index('slug');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
