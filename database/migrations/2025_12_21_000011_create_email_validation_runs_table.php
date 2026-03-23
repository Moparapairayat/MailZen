<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_validation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('list_id')->constrained('email_lists')->onDelete('cascade');
            $table->foreignId('tool_id')->nullable()->constrained('email_validation_tools')->nullOnDelete();

            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->enum('invalid_action', ['none', 'unsubscribe', 'mark_spam', 'delete'])->default('none');

            $table->unsignedInteger('total_emails')->default(0);
            $table->unsignedInteger('processed_count')->default(0);
            $table->unsignedInteger('deliverable_count')->default(0);
            $table->unsignedInteger('undeliverable_count')->default(0);
            $table->unsignedInteger('accept_all_count')->default(0);
            $table->unsignedInteger('unknown_count')->default(0);
            $table->unsignedInteger('error_count')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index('list_id');
            $table->index('tool_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_validation_runs');
    }
};
