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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('timezone')->default('UTC');
            $table->string('language')->default('en');
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('country')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('quota', 15, 2)->default(0);
            $table->decimal('quota_usage', 15, 2)->default(0);
            $table->integer('max_lists')->default(0);
            $table->integer('max_subscribers')->default(0);
            $table->integer('max_campaigns')->default(0);
            $table->date('expires_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('email');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

