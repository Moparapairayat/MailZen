<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_login_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ip_geolocation_id')->nullable()->constrained('ip_geolocations')->nullOnDelete();
            $table->string('ip', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();

            $table->index(['customer_id', 'logged_in_at']);
            $table->index('ip_geolocation_id');
            $table->index('logged_in_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_login_events');
    }
};
