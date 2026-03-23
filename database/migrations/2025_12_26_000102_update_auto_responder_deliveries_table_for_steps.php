<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $uniqueStepOrderIndex = 'ar_deliv_ar_sub_step_uq';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('auto_responder_deliveries', 'auto_responder_run_id')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->foreignId('auto_responder_run_id')->nullable()->after('id')->constrained('auto_responder_runs')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('auto_responder_deliveries', 'auto_responder_step_id')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->foreignId('auto_responder_step_id')->nullable()->after('auto_responder_id')->constrained('auto_responder_steps')->onDelete('cascade');
            });
        }

        if (!Schema::hasColumn('auto_responder_deliveries', 'step_order')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->unsignedInteger('step_order')->default(1)->after('auto_responder_step_id');
            });
        }

        DB::transaction(function () {
            $rows = DB::table('auto_responder_deliveries')
                ->select('id', 'auto_responder_id', 'subscriber_id')
                ->get();

            foreach ($rows as $row) {
                $stepId = DB::table('auto_responder_steps')
                    ->where('auto_responder_id', $row->auto_responder_id)
                    ->where('step_order', 1)
                    ->value('id');

                $runId = DB::table('auto_responder_runs')
                    ->where('auto_responder_id', $row->auto_responder_id)
                    ->where('subscriber_id', $row->subscriber_id)
                    ->value('id');

                DB::table('auto_responder_deliveries')
                    ->where('id', $row->id)
                    ->update([
                        'auto_responder_step_id' => $stepId,
                        'step_order' => 1,
                        'auto_responder_run_id' => $runId,
                    ]);
            }
        });

        if (!$this->indexExists('auto_responder_deliveries', $this->uniqueStepOrderIndex)
            && !$this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_id_subscriber_id_step_order_unique')) {
            $indexName = $this->uniqueStepOrderIndex;
            Schema::table('auto_responder_deliveries', function (Blueprint $table) use ($indexName) {
                $table->unique(['auto_responder_id', 'subscriber_id', 'step_order'], $indexName);
            });
        }

        if (!$this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_run_id_status_index')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->index(['auto_responder_run_id', 'status']);
            });
        }

        if (!$this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_step_id_status_index')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->index(['auto_responder_step_id', 'status']);
            });
        }

        if ($this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_id_subscriber_id_unique')) {
            if (!$this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_id_index')) {
                Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                    $table->index('auto_responder_id');
                });
            }

            if (!$this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_subscriber_id_index')) {
                Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                    $table->index('subscriber_id');
                });
            }

            $autoResponderFk = $this->foreignKeyName('auto_responder_deliveries', 'auto_responder_id');
            $subscriberFk = $this->foreignKeyName('auto_responder_deliveries', 'subscriber_id');

            if ($autoResponderFk || $subscriberFk) {
                Schema::table('auto_responder_deliveries', function (Blueprint $table) use ($autoResponderFk, $subscriberFk) {
                    if ($autoResponderFk) {
                        $table->dropForeign($autoResponderFk);
                    }
                    if ($subscriberFk) {
                        $table->dropForeign($subscriberFk);
                    }
                });
            }

            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->dropUnique('auto_responder_deliveries_auto_responder_id_subscriber_id_unique');
            });

            if ($autoResponderFk || $subscriberFk) {
                Schema::table('auto_responder_deliveries', function (Blueprint $table) use ($autoResponderFk, $subscriberFk) {
                    if ($autoResponderFk) {
                        $table->foreign('auto_responder_id')->references('id')->on('auto_responders')->onDelete('cascade');
                    }
                    if ($subscriberFk) {
                        $table->foreign('subscriber_id')->references('id')->on('list_subscribers')->onDelete('cascade');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!$this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_id_subscriber_id_unique')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->unique(['auto_responder_id', 'subscriber_id']);
            });
        }

        if ($this->indexExists('auto_responder_deliveries', $this->uniqueStepOrderIndex)) {
            $indexName = $this->uniqueStepOrderIndex;
            Schema::table('auto_responder_deliveries', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } elseif ($this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_id_subscriber_id_step_order_unique')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->dropUnique('auto_responder_deliveries_auto_responder_id_subscriber_id_step_order_unique');
            });
        }

        if ($this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_run_id_status_index')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->dropIndex('auto_responder_deliveries_auto_responder_run_id_status_index');
            });
        }

        if ($this->indexExists('auto_responder_deliveries', 'auto_responder_deliveries_auto_responder_step_id_status_index')) {
            Schema::table('auto_responder_deliveries', function (Blueprint $table) {
                $table->dropIndex('auto_responder_deliveries_auto_responder_step_id_status_index');
            });
        }

        Schema::table('auto_responder_deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('auto_responder_deliveries', 'auto_responder_run_id')) {
                $table->dropForeign(['auto_responder_run_id']);
            }
            if (Schema::hasColumn('auto_responder_deliveries', 'auto_responder_step_id')) {
                $table->dropForeign(['auto_responder_step_id']);
            }
        });

        Schema::table('auto_responder_deliveries', function (Blueprint $table) {
            $columns = [];
            foreach (['auto_responder_run_id', 'auto_responder_step_id', 'step_order'] as $column) {
                if (Schema::hasColumn('auto_responder_deliveries', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() !== 'mysql') {
            return false;
        }

        $databaseName = $connection->getDatabaseName();

        $result = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$databaseName, $table, $index]
        );

        return (int) ($result[0]->count ?? 0) > 0;
    }

    protected function foreignKeyName(string $table, string $column): ?string
    {
        $connection = Schema::getConnection();
        if ($connection->getDriverName() !== 'mysql') {
            return null;
        }

        $databaseName = $connection->getDatabaseName();

        $result = $connection->select(
            "SELECT CONSTRAINT_NAME as name FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL LIMIT 1",
            [$databaseName, $table, $column]
        );

        $name = $result[0]->name ?? null;
        return is_string($name) && $name !== '' ? $name : null;
    }
};
