<?php

namespace App\Services;

use App\Models\IpGeolocation;
use Illuminate\Support\Facades\Http;

class IpGeolocationService
{
    public function lookup(string $ip): ?IpGeolocation
    {
        $ip = trim($ip);
        if ($ip === '') {
            return null;
        }

        if ($this->isPrivateOrInvalidIp($ip)) {
            return null;
        }

        $existing = IpGeolocation::query()->where('ip', $ip)->first();
        if ($existing) {
            return $existing;
        }

        $token = config('services.ipinfo.token');
        if (!is_string($token) || $token === '') {
            return null;
        }

        $response = Http::timeout(5)
            ->acceptJson()
            ->get('https://ipinfo.io/' . urlencode($ip) . '/json', [
                'token' => $token,
            ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        if (!is_array($data)) {
            return null;
        }

        $loc = is_string($data['loc'] ?? null) ? $data['loc'] : null;
        $lat = null;
        $lng = null;
        if ($loc && str_contains($loc, ',')) {
            [$latStr, $lngStr] = array_pad(explode(',', $loc, 2), 2, null);
            $lat = is_numeric($latStr) ? (float) $latStr : null;
            $lng = is_numeric($lngStr) ? (float) $lngStr : null;
        }

        return IpGeolocation::create([
            'ip' => $ip,
            'country' => $data['country'] ?? null,
            'country_code' => $data['country'] ?? null,
            'region' => $data['region'] ?? null,
            'city' => $data['city'] ?? null,
            'latitude' => $lat,
            'longitude' => $lng,
            'timezone' => $data['timezone'] ?? null,
            'org' => $data['org'] ?? null,
            'raw' => $data,
            'looked_up_at' => now(),
        ]);
    }

    private function isPrivateOrInvalidIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return true;
        }

        // Exclude private/reserved ranges
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
