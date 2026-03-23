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
        Schema::create('list_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('rules'); // Array of rule conditions
            $table->integer('subscribers_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('list_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_segments');
    }
};
