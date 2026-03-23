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
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_path')->nullable()->after('status');
            $table->text('bio')->nullable()->after('avatar_path');
            $table->string('website_url')->nullable()->after('bio');
            $table->string('twitter_url')->nullable()->after('website_url');
            $table->string('facebook_url')->nullable()->after('twitter_url');
            $table->string('linkedin_url')->nullable()->after('facebook_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path',
                'bio',
                'website_url',
                'twitter_url',
                'facebook_url',
                'linkedin_url',
            ]);
        });
    }
};
