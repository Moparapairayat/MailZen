<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('auto_responders')
            ->whereIn('trigger', ['date_field', 'custom'])
            ->update([
                'trigger' => 'subscriber_added',
                'trigger_settings' => null,
            ]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE auto_responders MODIFY COLUMN `trigger` ENUM('subscriber_added', 'subscriber_confirmed', 'subscriber_unsubscribed', 'mail_opened', 'mail_clicked') DEFAULT 'subscriber_confirmed'");
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE auto_responders MODIFY COLUMN `trigger` ENUM('subscriber_added', 'subscriber_confirmed', 'subscriber_unsubscribed', 'date_field', 'custom') DEFAULT 'subscriber_confirmed'");
        }
    }
};
