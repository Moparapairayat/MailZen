<?php

namespace App\Services\EmailValidation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class SnapvalidClient
{
    public function uploadBulkEmails(string $apiKey, string $content, string $filename = 'emails.txt'): array
    {
        try {
            $url = 'https://app.snapvalid.com/api/upload-bulk-emails';
            $response = Http::timeout(60)
                ->retry(2, 200, null, false)
                ->asMultipart()
                ->attach('file', $content, $filename)
                ->acceptJson()
                ->post($url . '?' . http_build_query(['api_key' => $apiKey, 'apikey' => $apiKey]));

            if (!$response->successful()) {
                $json = $response->json();
                $apiMessage = is_array($json) && is_string($json['message'] ?? null) ? $json['message'] : null;

                return [
                    'success' => false,
                    'file_uploads_id' => null,
                    'message' => $apiMessage
                        ? "Snapvalid bulk upload failed ({$response->status()}): {$apiMessage}"
                        : "Snapvalid bulk upload failed ({$response->status()}).",
                    'raw' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ],
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'file_uploads_id' => null,
                    'message' => 'Snapvalid bulk upload returned invalid JSON.',
                    'raw' => ['body' => $response->body()],
                ];
            }

            $id = $data['file_uploads_id'] ?? $data['file_upload_id'] ?? $data['id'] ?? null;
            $id = is_numeric($id) ? (int) $id : null;

            $message = null;
            if (is_string($data['message'] ?? null)) {
                $message = $data['message'];
            } elseif (is_string($data['msg'] ?? null)) {
                $message = $data['msg'];
            }

            return [
                'success' => $id !== null,
                'file_uploads_id' => $id,
                'message' => $message,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'file_uploads_id' => null,
                'message' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    public function checkQueueProgress(string $apiKey): array
    {
        try {
            $response = Http::timeout(20)
                ->retry(2, 200, null, false)
                ->get('https://app.snapvalid.com/api/check-queue-progress', [
                    'api_key' => $apiKey,
                ]);

            if (!$response->successful()) {
                $json = $response->json();
                $apiMessage = is_array($json) && is_string($json['message'] ?? null) ? $json['message'] : null;

                return [
                    'success' => false,
                    'remaining' => null,
                    'message' => $apiMessage
                        ? "Snapvalid queue progress failed ({$response->status()}): {$apiMessage}"
                        : "Snapvalid queue progress failed ({$response->status()}).",
                    'raw' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ],
                ];
            }

            $data = $response->json();
            if (is_numeric($data)) {
                return [
                    'success' => true,
                    'remaining' => (int) $data,
                    'message' => null,
                    'raw' => $data,
                ];
            }

            if (!is_array($data)) {
                return [
                    'success' => false,
                    'remaining' => null,
                    'message' => 'Snapvalid queue progress returned invalid JSON.',
                    'raw' => ['body' => $response->body()],
                ];
            }

            $message = is_string($data['message'] ?? null) ? trim((string) $data['message']) : null;
            if ($message !== null && strcasecmp($message, 'No jobs in the queue') === 0) {
                return [
                    'success' => true,
                    'remaining' => 0,
                    'message' => $message,
                    'raw' => $data,
                ];
            }

            $remaining = $data['queue'] ?? $data['remaining'] ?? $data['progress'] ?? null;
            $remaining = is_numeric($remaining) ? (int) $remaining : null;

            return [
                'success' => $remaining !== null,
                'remaining' => $remaining,
                'message' => $message,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'remaining' => null,
                'message' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    public function downloadBulkResult(string $apiKey, int $fileUploadsId, string $typeDownload = '.csv'): array
    {
        try {
            $response = Http::timeout(60)
                ->retry(2, 200, null, false)
                ->get('https://app.snapvalid.com/api/downloadCsv', [
                    'api_key' => $apiKey,
                    'file_uploads_id' => $fileUploadsId,
                    'typeDownload' => $typeDownload,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'body' => null,
                    'message' => "Snapvalid bulk download failed ({$response->status()}).",
                    'raw' => [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ],
                ];
            }

            return [
                'success' => true,
                'body' => $response->body(),
                'message' => null,
                'raw' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'body' => null,
                'message' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    public function verify(string $apiKey, string $email): array
    {
        $email = trim($email);

        $rateLimitKey = 'snapvalid:verify:' . sha1($apiKey);
        $maxPerMinute = 100;
        $decaySeconds = 60;

        while (RateLimiter::tooManyAttempts($rateLimitKey, $maxPerMinute)) {
            $wait = RateLimiter::availableIn($rateLimitKey);
            sleep((int) max(1, min($wait, 10)));
        }

        RateLimiter::hit($rateLimitKey, $decaySeconds);

        try {
            $response = null;
            $last429Body = null;
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $response = Http::timeout(20)
                    ->retry(2, 500, null, false)
                    ->acceptJson()
                    ->get('https://app.snapvalid.com/api/v1/verify', [
                        'apikey' => $apiKey,
                        'api_key' => $apiKey,
                        'email' => $email,
                    ]);

                if ($response->status() !== 429) {
                    break;
                }

                $last429Body = $response->body();
                $retryAfter = (int) ($response->header('Retry-After') ?? 0);
                $sleepSeconds = $retryAfter > 0 ? $retryAfter : (int) min(60, (5 * ($attempt + 1)));
                sleep(max(1, $sleepSeconds));
            }

            if ($response && $response->status() === 429) {
                return [
                    'success' => false,
                    'result' => 'unknown',
                    'message' => 'Snapvalid rate limited (HTTP 429).',
                    'flags' => null,
                    'raw' => [
                        'status' => 429,
                        'body' => $last429Body,
                    ],
                ];
            }

            if (!$response->successful()) {
                $json = $response->json();
                $apiMessage = is_array($json) && is_string($json['message'] ?? null) ? $json['message'] : null;
                $body = $response->body();
                if ($apiMessage) {
                    $message = "Snapvalid request failed ({$response->status()}): {$apiMessage}";
                } else {
                    $message = "Snapvalid request failed ({$response->status()}).";
                }

                return [
                    'success' => false,
                    'result' => null,
                    'message' => $message,
                    'flags' => null,
                    'raw' => [
                        'status' => $response->status(),
                        'body' => $body,
                    ],
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'result' => null,
                    'message' => 'Snapvalid returned invalid JSON.',
                    'flags' => null,
                    'raw' => ['body' => $response->body()],
                ];
            }

            $success = (bool) ($data['success'] ?? false);
            $result = is_string($data['result'] ?? null) ? $data['result'] : null;
            if (is_string($result)) {
                $result = strtolower(trim($result));
                $result = str_replace('-', '_', $result);
            }

            $flags = array_intersect_key($data, array_flip([
                'accept_all',
                'role',
                'free_email',
                'disposable',
                'spamtrap',
            ]));

            return [
                'success' => $success,
                'result' => $result,
                'message' => is_string($data['message'] ?? null) ? $data['message'] : null,
                'flags' => $flags,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            if (is_string($message) && str_contains($message, 'cURL error 28')) {
                return [
                    'success' => false,
                    'result' => 'unknown',
                    'message' => $message,
                    'flags' => null,
                    'raw' => [
                        'exception' => $message,
                    ],
                ];
            }

            return [
                'success' => false,
                'result' => null,
                'message' => $message,
                'flags' => null,
                'raw' => null,
            ];
        }
    }
}
