<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::transaction(function () {
                DB::statement(<<<'SQL'
CREATE TABLE subscription_forms__tmp (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    list_id INTEGER NOT NULL,
    name VARCHAR NOT NULL,
    title VARCHAR NULL,
    type VARCHAR NOT NULL DEFAULT 'embedded',
    builder VARCHAR NOT NULL DEFAULT 'basic',
    slug VARCHAR NOT NULL,
    description TEXT NULL,
    html_content TEXT NULL,
    plain_text_content TEXT NULL,
    builder_data TEXT NULL,
    fields TEXT NULL,
    settings TEXT NULL,
    gdpr_checkbox TINYINT NOT NULL DEFAULT 0,
    gdpr_text TEXT NULL,
    is_active TINYINT NOT NULL DEFAULT 1,
    submissions_count INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    FOREIGN KEY(list_id) REFERENCES email_lists(id) ON DELETE CASCADE
);
SQL);

                DB::statement(<<<'SQL'
INSERT INTO subscription_forms__tmp (
    id,
    list_id,
    name,
    title,
    type,
    builder,
    slug,
    description,
    html_content,
    plain_text_content,
    builder_data,
    fields,
    settings,
    gdpr_checkbox,
    gdpr_text,
    is_active,
    submissions_count,
    created_at,
    updated_at,
    deleted_at
)
SELECT
    id,
    list_id,
    name,
    title,
    type,
    builder,
    slug,
    description,
    html_content,
    plain_text_content,
    builder_data,
    fields,
    settings,
    gdpr_checkbox,
    gdpr_text,
    is_active,
    submissions_count,
    created_at,
    updated_at,
    deleted_at
FROM subscription_forms;
SQL);

                DB::statement('DROP TABLE subscription_forms');
                DB::statement('ALTER TABLE subscription_forms__tmp RENAME TO subscription_forms');

                DB::statement("CREATE UNIQUE INDEX subscription_forms_slug_unique ON subscription_forms (slug)");
                DB::statement("CREATE INDEX subscription_forms_list_id_index ON subscription_forms (list_id)");
                DB::statement("CREATE INDEX subscription_forms_slug_index ON subscription_forms (slug)");
                DB::statement("CREATE INDEX subscription_forms_is_active_index ON subscription_forms (is_active)");
            });

            DB::statement('PRAGMA foreign_keys=ON');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscription_forms MODIFY builder ENUM('basic','unlayer','colt') NOT NULL DEFAULT 'basic'");
            return;
        }

        // Other drivers: remove enum constraint by switching to string.
        Schema::table('subscription_forms', function ($table) {
            $table->string('builder')->default('basic')->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=OFF');

            DB::transaction(function () {
                DB::statement(<<<'SQL'
CREATE TABLE subscription_forms__tmp (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    list_id INTEGER NOT NULL,
    name VARCHAR NOT NULL,
    title VARCHAR NULL,
    type VARCHAR NOT NULL DEFAULT 'embedded',
    builder VARCHAR NOT NULL CHECK (builder IN ('basic','unlayer')) DEFAULT 'basic',
    slug VARCHAR NOT NULL,
    description TEXT NULL,
    html_content TEXT NULL,
    plain_text_content TEXT NULL,
    builder_data TEXT NULL,
    fields TEXT NULL,
    settings TEXT NULL,
    gdpr_checkbox TINYINT NOT NULL DEFAULT 0,
    gdpr_text TEXT NULL,
    is_active TINYINT NOT NULL DEFAULT 1,
    submissions_count INTEGER NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL,
    FOREIGN KEY(list_id) REFERENCES email_lists(id) ON DELETE CASCADE
);
SQL);

                DB::statement(<<<'SQL'
INSERT INTO subscription_forms__tmp (
    id,
    list_id,
    name,
    title,
    type,
    builder,
    slug,
    description,
    html_content,
    plain_text_content,
    builder_data,
    fields,
    settings,
    gdpr_checkbox,
    gdpr_text,
    is_active,
    submissions_count,
    created_at,
    updated_at,
    deleted_at
)
SELECT
    id,
    list_id,
    name,
    title,
    type,
    CASE WHEN builder IN ('basic','unlayer') THEN builder ELSE 'basic' END as builder,
    slug,
    description,
    html_content,
    plain_text_content,
    builder_data,
    fields,
    settings,
    gdpr_checkbox,
    gdpr_text,
    is_active,
    submissions_count,
    created_at,
    updated_at,
    deleted_at
FROM subscription_forms;
SQL);

                DB::statement('DROP TABLE subscription_forms');
                DB::statement('ALTER TABLE subscription_forms__tmp RENAME TO subscription_forms');

                DB::statement("CREATE UNIQUE INDEX subscription_forms_slug_unique ON subscription_forms (slug)");
                DB::statement("CREATE INDEX subscription_forms_list_id_index ON subscription_forms (list_id)");
                DB::statement("CREATE INDEX subscription_forms_slug_index ON subscription_forms (slug)");
                DB::statement("CREATE INDEX subscription_forms_is_active_index ON subscription_forms (is_active)");
            });

            DB::statement('PRAGMA foreign_keys=ON');

            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE subscription_forms MODIFY builder ENUM('basic','unlayer') NOT NULL DEFAULT 'basic'");
            return;
        }

        Schema::table('subscription_forms', function ($table) {
            $table->enum('builder', ['basic', 'unlayer'])->default('basic')->change();
        });
    }
};
