<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE campaigns MODIFY COLUMN status " .
            "ENUM('draft', 'queued', 'scheduled', 'running', 'paused', 'completed', 'failed') DEFAULT 'draft'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        // Revert back to the previous enum set (without queued)
        DB::statement(
            "ALTER TABLE campaigns MODIFY COLUMN status " .
            "ENUM('draft', 'scheduled', 'running', 'paused', 'completed', 'failed') DEFAULT 'draft'"
        );
    }
};
