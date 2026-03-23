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
        Schema::create('reply_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('reply_domain')->nullable();
            $table->enum('protocol', ['imap', 'pop3'])->default('imap');
            $table->string('hostname');
            $table->integer('port')->default(993);
            $table->enum('encryption', ['ssl', 'tls', 'none'])->default('ssl');
            $table->string('username');
            $table->text('password');
            $table->string('mailbox')->default('INBOX');
            $table->boolean('active')->default(true);
            $table->boolean('delete_after_processing')->default(false);
            $table->integer('max_emails_per_batch')->default(100);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reply_servers');
    }
};
