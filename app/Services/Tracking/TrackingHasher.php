<?php

namespace App\Services\Tracking;

use App\Models\Campaign;
use App\Models\ListSubscriber;

class TrackingHasher
{
    protected string $secret;

    public function __construct()
    {
        $this->secret = config('app.key');
    }

    public function encodeCampaign(Campaign $campaign): string
    {
        return $this->encodeId('c', $campaign->id);
    }

    public function encodeSubscriber(ListSubscriber $subscriber): string
    {
        return $this->encodeId('s', $subscriber->id);
    }

    public function decodeCampaign(string $hash): ?Campaign
    {
        $id = $this->decodeId('c', $hash);
        if (!$id) {
            return null;
        }

        return Campaign::find($id);
    }

    public function decodeSubscriber(string $hash, Campaign $campaign): ?ListSubscriber
    {
        $id = $this->decodeId('s', $hash);
        if (!$id) {
            return null;
        }

        $subscriber = ListSubscriber::find($id);

        if (!$subscriber || $subscriber->list_id !== $campaign->list_id) {
            return null;
        }

        return $subscriber;
    }

    protected function encodeId(string $prefix, int $id): string
    {
        $payload = $prefix . '|' . $id;
        $hmac = hash_hmac('sha256', $payload, $this->secret);

        return rtrim(strtr(base64_encode($payload . '|' . $hmac), '+/', '-_'), '=');
    }

    protected function decodeId(string $expectedPrefix, string $hash): ?int
    {
        $decoded = base64_decode(strtr($hash, '-_', '+/'), true);
        if (!$decoded) {
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return null;
        }

        [$prefix, $id, $hmac] = $parts;

        if ($prefix !== $expectedPrefix || !ctype_digit($id)) {
            return null;
        }

        $payload = $prefix . '|' . $id;
        $expectedHmac = hash_hmac('sha256', $payload, $this->secret);

        if (!hash_equals($expectedHmac, $hmac)) {
            return null;
        }

        return (int) $id;
    }
}


