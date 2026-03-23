<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('auto_responders')) {
            return;
        }

        if (Schema::hasColumn('auto_responders', 'automation_id')) {
            return;
        }

        Schema::table('auto_responders', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver === 'sqlite' || !Schema::hasTable('automations')) {
                $table->unsignedBigInteger('automation_id')->nullable()->after('customer_id');
                $table->index(['automation_id']);
                return;
            }

            $table->foreignId('automation_id')->nullable()->after('customer_id')->constrained('automations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('auto_responders')) {
            return;
        }

        if (!Schema::hasColumn('auto_responders', 'automation_id')) {
            return;
        }

        Schema::table('auto_responders', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver !== 'sqlite') {
                $table->dropForeign(['automation_id']);
            }

            $table->dropColumn('automation_id');
        });
    }
};
