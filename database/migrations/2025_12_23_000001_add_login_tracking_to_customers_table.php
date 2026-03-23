<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('last_login_ip')->nullable()->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('last_login_ip');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['last_login_ip', 'last_login_at']);
        });
    }
};
