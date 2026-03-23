<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('public_template_customer_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_template_id')->constrained('public_templates')->onDelete('cascade');
            $table->foreignId('customer_group_id')->constrained('customer_groups')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['public_template_id', 'customer_group_id'], 'ptcg_unique');
            $table->index('customer_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_template_customer_group');
    }
};
