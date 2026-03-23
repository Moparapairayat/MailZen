<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_servers', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_servers', function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
            $table->dropConstrainedForeignId('customer_id');
        });
    }
};
