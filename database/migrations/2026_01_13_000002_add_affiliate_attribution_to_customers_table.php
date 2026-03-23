<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('referred_by_affiliate_id')->nullable()->after('currency');
            $table->timestamp('referred_at')->nullable()->after('referred_by_affiliate_id');

            $table->index(['referred_by_affiliate_id']);
            $table->foreign('referred_by_affiliate_id')->references('id')->on('affiliates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['referred_by_affiliate_id']);
            $table->dropIndex(['referred_by_affiliate_id']);
            $table->dropColumn(['referred_by_affiliate_id', 'referred_at']);
        });
    }
};
