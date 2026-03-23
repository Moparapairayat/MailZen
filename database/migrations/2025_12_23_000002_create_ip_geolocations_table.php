<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ip_geolocations', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 64)->unique();
            $table->string('country')->nullable();
            $table->string('country_code', 8)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone')->nullable();
            $table->string('org')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('looked_up_at')->nullable();
            $table->timestamps();

            $table->index('country_code');
            $table->index('looked_up_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ip_geolocations');
    }
};
