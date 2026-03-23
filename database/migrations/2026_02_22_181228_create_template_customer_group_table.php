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
        Schema::create('template_customer_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates')->onDelete('cascade');
            $table->foreignId('customer_group_id')->constrained('customer_groups')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['template_id', 'customer_group_id'], 'tcg_unique');
            $table->index('customer_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('template_customer_group');
    }
};
