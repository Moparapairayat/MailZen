<?php

namespace App\Http\Middleware;

use App\Models\WordPressIntegration;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class VerifyWordPressSignature
{
    public function handle(Request $request, Closure $next)
    {
        $customer = $request->user('sanctum');
        if (!$customer) {
            abort(401);
        }

        $integration = WordPressIntegration::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'signing_secret' => Str::random(64),
                'last_rotated_at' => now(),
            ]
        );

        $timestamp = (string) $request->header('X-MailZen-Timestamp', '');
        $signature = (string) $request->header('X-MailZen-Signature', '');

        if ($timestamp === '' || $signature === '') {
            return response()->json(['message' => 'Missing signature'], 401);
        }

        if (!ctype_digit($timestamp)) {
            return response()->json(['message' => 'Invalid timestamp'], 401);
        }

        $ts = (int) $timestamp;
        $now = time();
        $skew = abs($now - $ts);

        if ($skew > 300) {
            return response()->json(['message' => 'Signature expired'], 401);
        }

        $externalId = trim((string) $request->input('external_id', ''));
        if ($externalId === '') {
            return response()->json(['message' => 'external_id is required'], 422);
        }

        $cacheKey = 'wp_event:' . $customer->id . ':' . hash('sha256', $externalId);
        if (!Cache::add($cacheKey, 1, now()->addDay())) {
            return response()->json(['message' => 'Duplicate event'], 409);
        }

        $body = (string) $request->getContent();
        $bodyHash = hash('sha256', $body);

        $method = strtoupper($request->getMethod());
        $path = '/' . ltrim($request->path(), '/');

        $signed = $ts . "\n" . $method . "\n" . $path . "\n" . $bodyHash;
        $expected = hash_hmac('sha256', $signed, (string) $integration->signing_secret);

        if (!hash_equals($expected, $signature)) {
            Cache::forget($cacheKey);
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $request->attributes->set('wp_integration_id', $integration->id);

        return $next($request);
    }
}
