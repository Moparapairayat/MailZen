<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_validation_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_id')->constrained('email_validation_runs')->onDelete('cascade');
            $table->foreignId('subscriber_id')->nullable()->constrained('list_subscribers')->nullOnDelete();

            $table->string('email');
            $table->boolean('success')->default(true);
            $table->string('result')->nullable();
            $table->string('message')->nullable();
            $table->enum('action_taken', ['none', 'unsubscribe', 'mark_spam', 'delete'])->default('none');

            $table->json('flags')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('validated_at')->nullable();

            $table->timestamps();

            $table->unique(['run_id', 'email']);
            $table->index('subscriber_id');
            $table->index('result');
            $table->index('action_taken');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_validation_run_items');
    }
};
