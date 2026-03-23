<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'storage_driver'],
            [
                'category' => 'storage',
                'value' => 'local',
                'type' => 'string',
                'description' => 'Active storage provider (local, s3, wasabi, gcs).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'storage_public_root'],
            [
                'category' => 'storage',
                'value' => 'public',
                'type' => 'string',
                'description' => 'Root folder/prefix to store public assets under (optional).',
                'is_public' => false,
            ]
        );

        // AWS S3
        Setting::firstOrCreate(
            ['key' => 's3_key'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'AWS Access Key ID.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 's3_secret'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'AWS Secret Access Key (leave blank when saving to keep existing).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 's3_region'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'AWS Region (e.g., us-east-1).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 's3_bucket'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'AWS Bucket name.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 's3_endpoint'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'AWS custom endpoint (optional).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 's3_url'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'AWS public base URL / CDN URL (optional).',
                'is_public' => false,
            ]
        );

        // Wasabi (S3 compatible)
        Setting::firstOrCreate(
            ['key' => 'wasabi_key'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Wasabi Access Key ID.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'wasabi_secret'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Wasabi Secret Access Key (leave blank when saving to keep existing).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'wasabi_region'],
            [
                'category' => 'storage',
                'value' => 'us-east-1',
                'type' => 'string',
                'description' => 'Wasabi region (e.g., us-east-1, eu-central-1).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'wasabi_bucket'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Wasabi bucket name.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'wasabi_endpoint'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Wasabi endpoint (e.g., https://s3.us-east-1.wasabisys.com).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'wasabi_url'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Wasabi public base URL / CDN URL (optional).',
                'is_public' => false,
            ]
        );

        // Google Cloud Storage
        Setting::firstOrCreate(
            ['key' => 'gcs_project_id'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Google Cloud project ID.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'gcs_bucket'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Google Cloud Storage bucket name.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'gcs_key_file'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Absolute path to service account JSON file on the server.',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'gcs_path_prefix'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Optional object prefix inside the bucket (e.g., mailpurse).',
                'is_public' => false,
            ]
        );
        Setting::firstOrCreate(
            ['key' => 'gcs_url'],
            [
                'category' => 'storage',
                'value' => null,
                'type' => 'string',
                'description' => 'Optional public base URL (CDN) for GCS objects.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'storage_driver',
            'storage_public_root',
            's3_key',
            's3_secret',
            's3_region',
            's3_bucket',
            's3_endpoint',
            's3_url',
            'wasabi_key',
            'wasabi_secret',
            'wasabi_region',
            'wasabi_bucket',
            'wasabi_endpoint',
            'wasabi_url',
            'gcs_project_id',
            'gcs_bucket',
            'gcs_key_file',
            'gcs_path_prefix',
            'gcs_url',
        ])->delete();
    }
};
