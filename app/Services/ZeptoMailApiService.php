<?php

namespace App\Services;

use App\Models\DeliveryServer;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZeptoMailApiService
{
    private const BASE_URL = 'https://api.zeptomail.com';

    public function send(DeliveryServer $server, array $message): array
    {
        $settings = $server->settings ?? [];

        $mode = (string) ($settings['mode'] ?? 'raw');
        $mode = $mode !== '' ? $mode : 'raw';

        return match ($mode) {
            'template' => $this->sendTemplate($server, $message),
            default => $this->sendRaw($server, $message),
        };
    }

    public function sendRaw(DeliveryServer $server, array $message): array
    {
        return $this->post($server, '/v1.1/email', $this->buildRawPayload($server, $message));
    }

    public function sendTemplate(DeliveryServer $server, array $message): array
    {
        return $this->post($server, '/v1.1/email/template', $this->buildTemplatePayload($server, $message));
    }

    public function sendBatch(DeliveryServer $server, array $message, array $recipients): array
    {
        $settings = $server->settings ?? [];

        $mode = (string) ($settings['mode'] ?? 'raw');
        $mode = $mode !== '' ? $mode : 'raw';

        return match ($mode) {
            'template' => $this->sendBatchTemplate($server, $message, $recipients),
            default => $this->sendBatchRaw($server, $message, $recipients),
        };
    }

    public function sendBatchRaw(DeliveryServer $server, array $message, array $recipients): array
    {
        return $this->post($server, '/v1.1/email/batch', $this->buildBatchRawPayload($server, $message, $recipients));
    }

    public function sendBatchTemplate(DeliveryServer $server, array $message, array $recipients): array
    {
        return $this->post($server, '/v1.1/email/template/batch', $this->buildBatchTemplatePayload($server, $message, $recipients));
    }

    private function post(DeliveryServer $server, string $path, array $payload): array
    {
        $token = $this->getToken($server);

        $request = $this->http($token);

        $response = $request->post(self::BASE_URL . $path, $payload);

        if (!$response->successful()) {
            Log::warning('ZeptoMail API request failed', [
                'server_id' => $server->id,
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $message = $response->json('message') ?? $response->body();
            throw new \RuntimeException('ZeptoMail API error: ' . (is_string($message) ? $message : json_encode($message)));
        }

        return $response->json();
    }

    private function http(string $token): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => 'zoho-enczapikey ' . $token,
            ])
            ->timeout(30);
    }

    private function getToken(DeliveryServer $server): string
    {
        $settings = $server->settings ?? [];
        $token = $settings['send_mail_token'] ?? null;

        if (!is_string($token) || trim($token) === '') {
            throw new \InvalidArgumentException('ZeptoMail API token is missing.');
        }

        return trim($token);
    }

    private function buildRawPayload(DeliveryServer $server, array $message): array
    {
        $payload = [
            'from' => [
                'address' => (string) ($message['from_email'] ?? ''),
                'name' => (string) ($message['from_name'] ?? ''),
            ],
            'to' => [
                [
                    'email_address' => [
                        'address' => (string) ($message['to_email'] ?? ''),
                        'name' => (string) ($message['to_name'] ?? ''),
                    ],
                ],
            ],
            'subject' => (string) ($message['subject'] ?? ''),
            'track_clicks' => (bool) ($message['track_clicks'] ?? false),
            'track_opens' => (bool) ($message['track_opens'] ?? false),
            'client_reference' => $message['client_reference'] ?? null,
            'mime_headers' => $message['headers'] ?? null,
        ];

        $bounce = $this->bounceAddress($server, $message);
        if ($bounce) {
            $payload['bounce_address'] = $bounce;
        }

        $html = $message['htmlbody'] ?? null;
        $text = $message['textbody'] ?? null;

        if (is_string($html) && trim($html) !== '') {
            $payload['htmlbody'] = $html;
        } elseif (is_string($text) && trim($text) !== '') {
            $payload['textbody'] = $text;
        } else {
            throw new \InvalidArgumentException('ZeptoMail requires either htmlbody or textbody.');
        }

        $mergeInfo = $message['merge_info'] ?? null;
        if (is_array($mergeInfo) && !empty($mergeInfo)) {
            $payload['to'][0]['merge_info'] = $mergeInfo;
        }

        return $this->stripNulls($payload);
    }

    private function buildBatchTemplatePayload(DeliveryServer $server, array $message, array $recipients): array
    {
        $settings = $server->settings ?? [];

        $templateKey = $settings['template_key'] ?? null;
        $templateAlias = $settings['template_alias'] ?? null;

        $payload = $this->buildBatchRawPayload($server, $message, $recipients);

        unset($payload['htmlbody'], $payload['textbody']);

        if (is_string($templateKey) && trim($templateKey) !== '') {
            $payload['template_key'] = trim($templateKey);
        } elseif (is_string($templateAlias) && trim($templateAlias) !== '') {
            $payload['template_alias'] = trim($templateAlias);
        } else {
            throw new \InvalidArgumentException('ZeptoMail template mode requires template_key or template_alias in delivery server settings.');
        }

        return $this->stripNulls($payload);
    }

    private function buildBatchRawPayload(DeliveryServer $server, array $message, array $recipients): array
    {
        $payload = [
            'from' => [
                'address' => (string) ($message['from_email'] ?? ''),
                'name' => (string) ($message['from_name'] ?? ''),
            ],
            'to' => $recipients,
            'subject' => (string) ($message['subject'] ?? ''),
            'track_clicks' => (bool) ($message['track_clicks'] ?? false),
            'track_opens' => (bool) ($message['track_opens'] ?? false),
            'client_reference' => $message['client_reference'] ?? null,
            'mime_headers' => $message['headers'] ?? null,
        ];

        $bounce = $this->bounceAddress($server, $message);
        if ($bounce) {
            $payload['bounce_address'] = $bounce;
        }

        $html = $message['htmlbody'] ?? null;
        $text = $message['textbody'] ?? null;

        if (is_string($html) && trim($html) !== '') {
            $payload['htmlbody'] = $html;
        } elseif (is_string($text) && trim($text) !== '') {
            $payload['textbody'] = $text;
        } else {
            throw new \InvalidArgumentException('ZeptoMail requires either htmlbody or textbody.');
        }

        $mergeInfo = $message['merge_info'] ?? null;
        if (is_array($mergeInfo) && !empty($mergeInfo)) {
            $payload['merge_info'] = $mergeInfo;
        }

        return $this->stripNulls($payload);
    }

    private function buildTemplatePayload(DeliveryServer $server, array $message): array
    {
        $settings = $server->settings ?? [];

        $templateKey = $settings['template_key'] ?? null;
        $templateAlias = $settings['template_alias'] ?? null;

        $payload = $this->buildRawPayload($server, $message);

        unset($payload['htmlbody'], $payload['textbody']);

        if (is_string($templateKey) && trim($templateKey) !== '') {
            $payload['template_key'] = trim($templateKey);
        } elseif (is_string($templateAlias) && trim($templateAlias) !== '') {
            $payload['template_alias'] = trim($templateAlias);
        } else {
            throw new \InvalidArgumentException('ZeptoMail template mode requires template_key or template_alias in delivery server settings.');
        }

        return $this->stripNulls($payload);
    }

    private function bounceAddress(DeliveryServer $server, array $message): ?string
    {
        $settings = $server->settings ?? [];

        $bounce = $settings['bounce_address'] ?? null;
        if (is_string($bounce) && trim($bounce) !== '') {
            return trim($bounce);
        }

        $bounceFromMessage = $message['bounce_address'] ?? null;
        if (is_string($bounceFromMessage) && trim($bounceFromMessage) !== '') {
            return trim($bounceFromMessage);
        }

        return null;
    }

    private function stripNulls(array $payload): array
    {
        $payload = Arr::where($payload, fn ($v) => $v !== null);

        foreach ($payload as $k => $v) {
            if (is_array($v)) {
                $payload[$k] = $this->stripNulls($v);
            }
        }

        return $payload;
    }
}
