<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type')->default('page');
            $table->string('variant_key')->nullable();
            $table->string('status')->default('draft');
            $table->longText('html_content')->nullable();
            $table->json('builder_data')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('status');
            $table->index('variant_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
