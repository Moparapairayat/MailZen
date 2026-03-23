<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!$this->hasIndex('customers', 'customers_created_at_index')) {
                $table->index('created_at');
            }
        });

        Schema::table('list_subscribers', function (Blueprint $table) {
            if (!$this->hasIndex('list_subscribers', 'list_subscribers_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('list_subscribers', 'list_subscribers_list_id_created_at_index')) {
                $table->index(['list_id', 'created_at']);
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (!$this->hasIndex('subscriptions', 'subscriptions_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('subscriptions', 'subscriptions_cancelled_at_index')) {
                $table->index('cancelled_at');
            }
            if (!$this->hasIndex('subscriptions', 'subscriptions_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if (!$this->hasIndex('campaigns', 'campaigns_created_at_index')) {
                $table->index('created_at');
            }
            if (!$this->hasIndex('campaigns', 'campaigns_started_at_index')) {
                $table->index('started_at');
            }
        });

        Schema::table('email_lists', function (Blueprint $table) {
            if (!$this->hasIndex('email_lists', 'email_lists_created_at_index')) {
                $table->index('created_at');
            }
        });

        Schema::table('templates', function (Blueprint $table) {
            if (!$this->hasIndex('templates', 'templates_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if ($this->hasIndex('customers', 'customers_created_at_index')) {
                $table->dropIndex('customers_created_at_index');
            }
        });

        Schema::table('list_subscribers', function (Blueprint $table) {
            if ($this->hasIndex('list_subscribers', 'list_subscribers_created_at_index')) {
                $table->dropIndex('list_subscribers_created_at_index');
            }
            if ($this->hasIndex('list_subscribers', 'list_subscribers_list_id_created_at_index')) {
                $table->dropIndex('list_subscribers_list_id_created_at_index');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if ($this->hasIndex('subscriptions', 'subscriptions_created_at_index')) {
                $table->dropIndex('subscriptions_created_at_index');
            }
            if ($this->hasIndex('subscriptions', 'subscriptions_cancelled_at_index')) {
                $table->dropIndex('subscriptions_cancelled_at_index');
            }
            if ($this->hasIndex('subscriptions', 'subscriptions_status_created_at_index')) {
                $table->dropIndex('subscriptions_status_created_at_index');
            }
        });

        Schema::table('campaigns', function (Blueprint $table) {
            if ($this->hasIndex('campaigns', 'campaigns_created_at_index')) {
                $table->dropIndex('campaigns_created_at_index');
            }
            if ($this->hasIndex('campaigns', 'campaigns_started_at_index')) {
                $table->dropIndex('campaigns_started_at_index');
            }
        });

        Schema::table('email_lists', function (Blueprint $table) {
            if ($this->hasIndex('email_lists', 'email_lists_created_at_index')) {
                $table->dropIndex('email_lists_created_at_index');
            }
        });

        Schema::table('templates', function (Blueprint $table) {
            if ($this->hasIndex('templates', 'templates_created_at_index')) {
                $table->dropIndex('templates_created_at_index');
            }
        });
    }

    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $databaseName = $connection->getDatabaseName();
            $result = $connection->select(
                "SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $index]
            );

            return isset($result[0]) && (int) $result[0]->count > 0;
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
