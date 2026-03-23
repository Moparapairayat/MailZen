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
        Schema::create('subscription_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');
            $table->string('name');
            $table->string('type')->default('embedded'); // embedded, popup, api
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('fields')->nullable(); // Which fields to show
            $table->json('settings')->nullable(); // Form-specific settings
            $table->boolean('gdpr_checkbox')->default(false);
            $table->text('gdpr_text')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('submissions_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('list_id');
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_forms');
    }
};
