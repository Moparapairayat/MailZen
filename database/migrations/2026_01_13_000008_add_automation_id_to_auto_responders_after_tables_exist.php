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

        $driver = Schema::getConnection()->getDriverName();

        if (!Schema::hasColumn('auto_responders', 'automation_id')) {
            Schema::table('auto_responders', function (Blueprint $table) {
                $table->unsignedBigInteger('automation_id')->nullable()->after('customer_id');
                $table->index(['automation_id']);
            });
        }

        if ($driver === 'sqlite') {
            return;
        }

        if (!Schema::hasTable('automations')) {
            return;
        }

        try {
            Schema::table('auto_responders', function (Blueprint $table) {
                $table->foreign('automation_id', 'auto_responders_automation_id_foreign')
                    ->references('id')
                    ->on('automations')
                    ->nullOnDelete();
            });
        } catch (\Throwable $e) {
            // Ignore if the constraint already exists or cannot be added.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('auto_responders')) {
            return;
        }

        if (!Schema::hasColumn('auto_responders', 'automation_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        Schema::table('auto_responders', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                try {
                    $table->dropForeign('auto_responders_automation_id_foreign');
                } catch (\Throwable $e) {
                    // Ignore if missing.
                }
            }

            $table->dropColumn('automation_id');
        });
    }
};
