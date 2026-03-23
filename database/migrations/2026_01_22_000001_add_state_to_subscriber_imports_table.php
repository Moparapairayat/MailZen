<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriber_imports', function (Blueprint $table) {
            $table->json('headers')->nullable()->after('stored_path');
            $table->json('column_mapping')->nullable()->after('headers');
            $table->boolean('skip_duplicates')->default(true)->after('column_mapping');
            $table->boolean('update_existing')->default(false)->after('skip_duplicates');
            $table->unsignedBigInteger('file_offset')->default(0)->after('update_existing');
            $table->timestamp('locked_at')->nullable()->after('file_offset');
        });
    }

    public function down(): void
    {
        Schema::table('subscriber_imports', function (Blueprint $table) {
            $table->dropColumn([
                'headers',
                'column_mapping',
                'skip_duplicates',
                'update_existing',
                'file_offset',
                'locked_at',
            ]);
        });
    }
};
