<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_servers', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_servers', 'second_quota')) {
                $table->integer('second_quota')->default(0)->after('max_connection_messages');
            }

            if (!Schema::hasColumn('delivery_servers', 'minute_quota')) {
                $table->integer('minute_quota')->default(0)->after('second_quota');
            }
        });

        try {
            DB::statement("ALTER TABLE delivery_servers MODIFY type ENUM('smtp','sendmail','amazon-ses','mailgun','sendgrid','postmark','sparkpost','zeptomail','zeptomail-api') DEFAULT 'smtp'");
        } catch (\Throwable $e) {
        }
    }

    public function down(): void
    {
        Schema::table('delivery_servers', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_servers', 'minute_quota')) {
                $table->dropColumn('minute_quota');
            }

            if (Schema::hasColumn('delivery_servers', 'second_quota')) {
                $table->dropColumn('second_quota');
            }
        });

        try {
            DB::statement("ALTER TABLE delivery_servers MODIFY type ENUM('smtp','sendmail','amazon-ses','mailgun','sendgrid','postmark','sparkpost') DEFAULT 'smtp'");
        } catch (\Throwable $e) {
        }
    }
};
