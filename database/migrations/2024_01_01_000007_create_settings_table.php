<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            if (!Schema::hasColumn('settings', 'category')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->string('category')->index();
                });
            }

            if (!Schema::hasColumn('settings', 'key')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->string('key')->unique();
                });
            }

            if (!Schema::hasColumn('settings', 'value')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->text('value')->nullable();
                });
            }

            if (!Schema::hasColumn('settings', 'type')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->string('type')->default('string');
                });
            }

            if (!Schema::hasColumn('settings', 'description')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->text('description')->nullable();
                });
            }

            if (!Schema::hasColumn('settings', 'is_public')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->boolean('is_public')->default(false);
                });
            }

            if (!Schema::hasColumn('settings', 'created_at') || !Schema::hasColumn('settings', 'updated_at')) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->timestamps();
                });
            }

            $databaseName = DB::getDatabaseName();

            $existingIndexNames = collect(DB::select(
                'select distinct INDEX_NAME from information_schema.STATISTICS where TABLE_SCHEMA = ? and TABLE_NAME = ?',
                [$databaseName, 'settings']
            ))->pluck('INDEX_NAME')->all();

            if (!in_array('settings_category_index', $existingIndexNames, true)) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->index('category');
                });
            }

            $hasKeyUnique = (int) (DB::selectOne(
                'select count(*) as c from information_schema.STATISTICS where TABLE_SCHEMA = ? and TABLE_NAME = ? and COLUMN_NAME = ? and NON_UNIQUE = 0',
                [$databaseName, 'settings', 'key']
            )->c ?? 0) > 0;

            if (!$hasKeyUnique) {
                Schema::table('settings', function (Blueprint $table) {
                    $table->unique('key');
                });
            }

            return;
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('category')->index();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, array
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

