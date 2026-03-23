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
        Schema::create('translation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_locale_id')->constrained('translation_locales')->cascadeOnDelete();
            $table->string('group', 50)->default('*');
            $table->string('key', 191);
            $table->longText('text')->nullable();
            $table->timestamps();

            $table->unique(['translation_locale_id', 'group', 'key']);
            $table->index(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_lines');
    }
};
