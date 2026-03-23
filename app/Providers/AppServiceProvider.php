<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Schema::defaultStringLength(150);

        try {
            $prefix = Setting::get('storage_url_prefix', '');
        } catch (\Throwable $e) {
            $prefix = '';
        }

        $prefix = is_string($prefix) ? trim($prefix) : '';
        $prefix = trim($prefix, "/\t\n\r\0\x0B");
        $prefix = trim($prefix, '/');

        if ($prefix !== '') {
            $url = rtrim((string) config('app.url'), '/') . '/' . $prefix . '/storage';
        } else {
            $url = rtrim((string) config('app.url'), '/') . '/storage';
        }

        config(['filesystems.disks.public.url' => $url]);
    }
}
