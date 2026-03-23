<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique();
            $table->string('name')->nullable();
            $table->string('resource_type', 100)->nullable();
            $table->string('template_type', 100)->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('preview_image')->nullable();
            $table->string('preview_url')->nullable();

            $table->boolean('requires_license')->default(false);
            $table->string('plan', 50)->nullable();
            $table->string('builder', 50)->nullable();

            $table->json('categories')->nullable();
            $table->json('meta')->nullable();

            $table->longText('json_code')->nullable();
            $table->timestamp('json_fetched_at')->nullable();

            $table->timestamp('external_created_at')->nullable();
            $table->timestamp('external_updated_at')->nullable();

            $table->timestamps();

            $table->index(['resource_type', 'product_id']);
            $table->index(['requires_license', 'plan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_templates');
    }
};
