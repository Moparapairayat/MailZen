<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CronController extends Controller
{
    public function run(Request $request)
    {
        if (!Setting::get('cron_web_enabled', 0)) {
            return response('Cron is disabled.', 403);
        }

        $expectedToken = (string) Setting::get('cron_web_token', '');
        $providedToken = (string) $request->query('token', '');

        if ($expectedToken === '' || $providedToken === '' || !hash_equals($expectedToken, $providedToken)) {
            return response('Unauthorized.', 403);
        }

        $lock = Cache::lock('cron:web:run', 55);
        if (!$lock->get()) {
            return response('Already running.', 429);
        }

        try {
            $exitCode = Artisan::call('schedule:run');
            Setting::set('cron_last_run_at', now()->toIso8601String(), 'cron', 'string');

            return response('OK (exit code: ' . (int) $exitCode . ')', 200);
        } finally {
            try {
                $lock->release();
            } catch (\Throwable $e) {
            }
        }
    }
}
