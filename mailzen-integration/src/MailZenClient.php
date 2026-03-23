<?php

namespace MailZenIntegration;

class MailZenClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
        private ?string $signingSecret = null
    ) {
    }

    public static function fromSettings(array $settings): ?self
    {
        $baseUrl = isset($settings['base_url']) ? trim((string) $settings['base_url']) : '';
        $apiKey = isset($settings['api_key']) ? trim((string) $settings['api_key']) : '';
        $signingSecret = isset($settings['signing_secret']) ? trim((string) $settings['signing_secret']) : '';

        if ($baseUrl === '' || $apiKey === '') {
            return null;
        }

        return new self($baseUrl, $apiKey, ($signingSecret !== '' ? $signingSecret : null));
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->buildUrl($path, $query);

        $resp = wp_remote_get($url, [
            'timeout' => 10,
            'headers' => $this->headers(),
        ]);

        return $this->normalizeResponse($resp);
    }

    public function post(string $path, array $body): array
    {
        $url = $this->buildUrl($path);

        $headers = $this->headers();

        if ($path === '/api/v1/integrations/wordpress/events') {
            $sig = $this->signatureHeaders('POST', $path, $body);
            if ($sig) {
                $headers = array_merge($headers, $sig);
            }
        }

        $resp = wp_remote_post($url, [
            'timeout' => 10,
            'headers' => $headers,
            'body' => wp_json_encode($body),
        ]);

        return $this->normalizeResponse($resp);
    }

    public function fetchLists(): array
    {
        return $this->get('/api/v1/lists');
    }

    public function fetchSigningSecret(): array
    {
        $resp = $this->get('/api/v1/integrations/wordpress/connection');
        if (($resp['ok'] ?? false) && is_array($resp['data']['data'] ?? null)) {
            $secret = trim((string) ($resp['data']['data']['signing_secret'] ?? ''));
            if ($secret !== '') {
                $this->signingSecret = $secret;
            }
        }
        return $resp;
    }

    public function sendWordPressEvent(array $event): array
    {
        return $this->post('/api/v1/integrations/wordpress/events', $event);
    }

    private function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function signatureHeaders(string $method, string $path, array $body): ?array
    {
        $secret = $this->signingSecret;
        if (!$secret) {
            return null;
        }

        $ts = (string) time();
        $payload = wp_json_encode($body);
        $hash = hash('sha256', (string) $payload);

        $signed = $ts . "\n" . strtoupper($method) . "\n" . $path . "\n" . $hash;
        $sig = hash_hmac('sha256', $signed, $secret);

        return [
            'X-MailZen-Timestamp' => $ts,
            'X-MailZen-Signature' => $sig,
        ];
    }

    private function buildUrl(string $path, array $query = []): string
    {
        $base = rtrim($this->baseUrl, '/');
        $path = '/' . ltrim($path, '/');

        $url = $base . $path;

        if (!empty($query)) {
            $url = add_query_arg($query, $url);
        }

        return $url;
    }

    private function normalizeResponse($resp): array
    {
        if (is_wp_error($resp)) {
            return [
                'ok' => false,
                'status' => 0,
                'error' => $resp->get_error_message(),
                'data' => null,
            ];
        }

        $status = (int) wp_remote_retrieve_response_code($resp);
        $raw = (string) wp_remote_retrieve_body($resp);

        $data = null;
        if ($raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $data = $decoded;
            }
        }

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'error' => ($status >= 200 && $status < 300) ? null : $raw,
            'data' => $data,
        ];
    }
}
