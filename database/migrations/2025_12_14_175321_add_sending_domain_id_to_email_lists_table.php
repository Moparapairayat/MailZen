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
        Schema::table('email_lists', function (Blueprint $table) {
            $table->foreignId('sending_domain_id')->nullable()->after('customer_id')->constrained('sending_domains')->onDelete('set null');
            $table->index('sending_domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->dropForeign(['sending_domain_id']);
            $table->dropIndex(['sending_domain_id']);
            $table->dropColumn('sending_domain_id');
        });
    }
};
