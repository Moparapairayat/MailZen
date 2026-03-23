<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('public_template_categories')->nullOnDelete();
            $table->foreignId('created_by_admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'campaign', 'transactional', 'autoresponder'])->default('email');
            $table->enum('builder', ['grapesjs', 'unlayer'])->default('unlayer');
            $table->longText('html_content')->nullable();
            $table->longText('plain_text_content')->nullable();
            $table->json('builder_data')->nullable();
            $table->json('settings')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category_id');
            $table->index('type');
            $table->index('builder');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_templates');
    }
};
