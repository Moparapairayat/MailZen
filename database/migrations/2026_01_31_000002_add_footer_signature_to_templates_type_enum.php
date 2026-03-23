<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE templates MODIFY COLUMN type " .
            "ENUM('email', 'campaign', 'transactional', 'autoresponder', 'footer', 'signature') DEFAULT 'email'"
        );
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement(
            "ALTER TABLE templates MODIFY COLUMN type " .
            "ENUM('email', 'campaign', 'transactional', 'autoresponder') DEFAULT 'email'"
        );
    }
};
