<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('draft', 'pending', 'sending', 'sent', 'paused', 'cancelled', 'queued', 'scheduled', 'running', 'completed', 'failed') DEFAULT 'draft'");
        }
        
        // Update existing status values to match new enum
        DB::table('campaigns')->where('status', 'pending')->update(['status' => 'draft']);
        DB::table('campaigns')->where('status', 'sending')->update(['status' => 'running']);
        DB::table('campaigns')->where('status', 'sent')->update(['status' => 'completed']);
        DB::table('campaigns')->where('status', 'cancelled')->update(['status' => 'failed']);

        Schema::table('campaigns', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver !== 'sqlite') {
                DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('draft', 'queued', 'scheduled', 'running', 'paused', 'completed', 'failed') DEFAULT 'draft'");
            }
            
            // Add scheduled_at if not exists
            if (!Schema::hasColumn('campaigns', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('send_at');
            }
            
            // Add failed_count if not exists
            if (!Schema::hasColumn('campaigns', 'failed_count')) {
                $table->integer('failed_count')->default(0)->after('clicked_count');
            }
            
            // Add index for scheduled_at if not exists
            if ($driver !== 'sqlite' && !$this->hasIndex('campaigns', 'campaigns_scheduled_at_index')) {
                $table->index('scheduled_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $driver = Schema::getConnection()->getDriverName();

            if ($driver !== 'sqlite') {
                DB::statement("ALTER TABLE campaigns MODIFY COLUMN status ENUM('draft', 'pending', 'sending', 'sent', 'paused', 'cancelled') DEFAULT 'draft'");
            }
            
            if (Schema::hasColumn('campaigns', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
            
            if (Schema::hasColumn('campaigns', 'failed_count')) {
                $table->dropColumn('failed_count');
            }
        });
    }

    /**
     * Check if index exists.
     */
    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $result = $connection->select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return !empty($result);
        }

        if ($driver === 'pgsql') {
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $index]
            );

            return isset($result[0]) && (int) $result[0]->count > 0;
        }

        return false;
    }
};

