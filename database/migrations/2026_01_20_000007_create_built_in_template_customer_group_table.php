<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('built_in_template_customer_group')) {
            if (!Schema::hasColumn('built_in_template_customer_group', 'built_in_template_setting_id')) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->unsignedBigInteger('built_in_template_setting_id');
                });
            }

            if (!Schema::hasColumn('built_in_template_customer_group', 'customer_group_id')) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->unsignedBigInteger('customer_group_id');
                });
            }

            $databaseName = DB::getDatabaseName();

            $existingIndexColumns = collect(DB::select(
                'select distinct COLUMN_NAME from information_schema.STATISTICS where TABLE_SCHEMA = ? and TABLE_NAME = ?',
                [$databaseName, 'built_in_template_customer_group']
            ))->pluck('COLUMN_NAME')->all();

            if (!in_array('built_in_template_setting_id', $existingIndexColumns, true)) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->index('built_in_template_setting_id', 'bitcg_bits_id_idx');
                });
            }

            if (!in_array('customer_group_id', $existingIndexColumns, true)) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->index('customer_group_id', 'bitcg_cg_id_idx');
                });
            }

            $existingIndexNames = collect(DB::select(
                'select distinct INDEX_NAME from information_schema.STATISTICS where TABLE_SCHEMA = ? and TABLE_NAME = ?',
                [$databaseName, 'built_in_template_customer_group']
            ))->pluck('INDEX_NAME')->all();

            if (!in_array('bitcg_unique', $existingIndexNames, true)) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->unique(['built_in_template_setting_id', 'customer_group_id'], 'bitcg_unique');
                });
            }

            $existingForeignKeyColumns = collect(DB::select(
                'select distinct COLUMN_NAME from information_schema.KEY_COLUMN_USAGE where TABLE_SCHEMA = ? and TABLE_NAME = ? and REFERENCED_TABLE_NAME is not null',
                [$databaseName, 'built_in_template_customer_group']
            ))->pluck('COLUMN_NAME')->all();

            if (!in_array('built_in_template_setting_id', $existingForeignKeyColumns, true)) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->foreign('built_in_template_setting_id', 'bitcg_bits_fk')
                        ->references('id')
                        ->on('built_in_template_settings')
                        ->onDelete('cascade');
                });
            }

            if (!in_array('customer_group_id', $existingForeignKeyColumns, true)) {
                Schema::table('built_in_template_customer_group', function (Blueprint $table) {
                    $table->foreign('customer_group_id', 'bitcg_cg_fk')
                        ->references('id')
                        ->on('customer_groups')
                        ->onDelete('cascade');
                });
            }

            return;
        }

        Schema::create('built_in_template_customer_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('built_in_template_setting_id');
            $table->unsignedBigInteger('customer_group_id');
            $table->timestamps();

            $table->index('built_in_template_setting_id', 'bitcg_bits_id_idx');
            $table->index('customer_group_id', 'bitcg_cg_id_idx');

            $table->foreign('built_in_template_setting_id', 'bitcg_bits_fk')
                ->references('id')
                ->on('built_in_template_settings')
                ->onDelete('cascade');

            $table->foreign('customer_group_id', 'bitcg_cg_fk')
                ->references('id')
                ->on('customer_groups')
                ->onDelete('cascade');

            $table->unique(['built_in_template_setting_id', 'customer_group_id'], 'bitcg_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('built_in_template_customer_group');
    }
};
