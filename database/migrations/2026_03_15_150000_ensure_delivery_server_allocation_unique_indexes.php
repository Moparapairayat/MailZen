<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureUniqueIndex(
            'customer_delivery_server',
            'cust_del_srv_unique',
            ['customer_id', 'delivery_server_id']
        );

        $this->ensureUniqueIndex(
            'customer_group_delivery_server',
            'cust_grp_del_srv_unique',
            ['customer_group_id', 'delivery_server_id']
        );
    }

    public function down(): void
    {
        $this->dropUniqueIndexIfExists('customer_group_delivery_server', 'cust_grp_del_srv_unique');
        $this->dropUniqueIndexIfExists('customer_delivery_server', 'cust_del_srv_unique');
    }

    private function ensureUniqueIndex(string $table, string $indexName, array $columns): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->unique($columns, $indexName);
        });
    }

    private function dropUniqueIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropUnique($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            return DB::table('information_schema.statistics')
                ->where('table_schema', DB::getDatabaseName())
                ->where('table_name', $table)
                ->where('index_name', $indexName)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }
};
