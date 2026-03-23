<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('built_in_template_settings', function (Blueprint $table) {
            $table->id();
            $table->string('builder')->default('unlayer');
            $table->string('template_key', 64);
            $table->string('relative_path');
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['builder', 'template_key'], 'bits_builder_key_unique');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('built_in_template_settings');
    }
};
