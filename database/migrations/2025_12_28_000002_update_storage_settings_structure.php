<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $existingStorageDriver = Setting::get('storage_driver', 'local');
        $defaultStorageDriver = 'local';

        if (is_string($existingStorageDriver)) {
            $candidate = strtolower(trim($existingStorageDriver));

            if (in_array($candidate, ['local', 's3', 'wasabi', 'gcs'], true)) {
                $defaultStorageDriver = $candidate;
            }
        }

        if (is_array($existingStorageDriver) && isset($existingStorageDriver['driver']) && is_string($existingStorageDriver['driver'])) {
            $candidate = strtolower(trim($existingStorageDriver['driver']));

            if (in_array($candidate, ['local', 's3', 'wasabi', 'gcs'], true)) {
                $defaultStorageDriver = $candidate;
            }
        }

        Setting::firstOrCreate(
            ['key' => 'default_storage_driver'],
            [
                'category' => 'general',
                'value' => $defaultStorageDriver,
                'type' => 'string',
                'description' => 'Default storage provider used for file uploads (local, s3, wasabi, gcs).',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'storage_local_enabled'],
            [
                'category' => 'storage',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Enable local storage provider.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'storage_s3_enabled'],
            [
                'category' => 'storage',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'Enable AWS S3 storage provider.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'storage_wasabi_enabled'],
            [
                'category' => 'storage',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'Enable Wasabi storage provider.',
                'is_public' => false,
            ]
        );

        Setting::firstOrCreate(
            ['key' => 'storage_gcs_enabled'],
            [
                'category' => 'storage',
                'value' => 0,
                'type' => 'boolean',
                'description' => 'Enable Google Cloud Storage provider.',
                'is_public' => false,
            ]
        );
    }

    public function down(): void
    {
        Setting::whereIn('key', [
            'default_storage_driver',
            'storage_local_enabled',
            'storage_s3_enabled',
            'storage_wasabi_enabled',
            'storage_gcs_enabled',
        ])->delete();
    }
};
