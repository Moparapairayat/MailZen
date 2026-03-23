<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->boolean('double_opt_in')->default(true)->after('opt_in');
            $table->string('default_subject')->nullable()->after('description');
            $table->string('company_name')->nullable()->after('default_subject');
            $table->text('company_address')->nullable()->after('company_name');
            $table->text('footer_text')->nullable()->after('company_address');
            $table->string('unsubscribe_redirect_url')->nullable()->after('unsubscribe_email_content');
            $table->boolean('gdpr_enabled')->default(false)->after('unsubscribe_redirect_url');
            $table->text('gdpr_text')->nullable()->after('gdpr_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_lists', function (Blueprint $table) {
            $table->dropColumn([
                'double_opt_in',
                'default_subject',
                'company_name',
                'company_address',
                'footer_text',
                'unsubscribe_redirect_url',
                'gdpr_enabled',
                'gdpr_text',
            ]);
        });
    }
};
