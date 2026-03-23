<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class GeminiClient
{
    private const DEFAULT_MODEL = 'gemini-2.0-flash';

    private function describeFailure($response): string
    {
        $status = null;
        $body = null;

        try {
            $status = $response ? $response->status() : null;
            $body = $response ? $response->body() : null;
        } catch (\Throwable $e) {
            $status = null;
            $body = null;
        }

        $message = null;
        try {
            $data = $response ? $response->json() : null;
            if (is_array($data) && is_array($data['error'] ?? null) && is_string($data['error']['message'] ?? null)) {
                $message = trim((string) $data['error']['message']);
            }
        } catch (\Throwable $e) {
            $message = null;
        }

        if (!is_string($message) || trim($message) === '') {
            if (is_string($body)) {
                $compact = trim(str_replace(["\r", "\n"], ' ', $body));
                if ($compact !== '') {
                    $message = substr($compact, 0, 500);
                }
            }
        }

        $prefix = 'Gemini request failed.';
        if (is_numeric($status)) {
            $prefix .= ' (HTTP ' . ((int) $status) . ')';
        }

        if (is_string($message) && trim($message) !== '') {
            return $prefix . ' ' . $message;
        }

        return $prefix;
    }

    private function listModels(string $apiKey, string $version): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(1, 200, null, false)
                ->withQueryParameters(['key' => $apiKey])
                ->get('https://generativelanguage.googleapis.com/' . $version . '/models');

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [];
            }

            $models = $data['models'] ?? null;
            return is_array($models) ? $models : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function pickModelFromList(array $models): ?string
    {
        $candidates = [];

        foreach ($models as $m) {
            if (!is_array($m)) {
                continue;
            }

            $name = is_string($m['name'] ?? null) ? trim((string) $m['name']) : '';
            if ($name === '') {
                continue;
            }

            $supported = $m['supportedGenerationMethods'] ?? null;
            if (is_array($supported) && !in_array('generateContent', $supported, true)) {
                continue;
            }

            $name = preg_replace('#^models/#', '', $name) ?? $name;
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $candidates[] = $name;
        }

        if (count($candidates) === 0) {
            return null;
        }

        $preferredPrefixes = [
            'gemini-2.0-flash',
            'gemini-2.0-pro',
            'gemini-1.5-flash',
            'gemini-1.5-pro',
            'gemini-1.0-pro',
            'gemini-pro',
        ];

        foreach ($preferredPrefixes as $prefix) {
            foreach ($candidates as $cand) {
                if (str_starts_with($cand, $prefix)) {
                    return $cand;
                }
            }
        }

        return $candidates[0];
    }

    private function normalizeModel(?string $model): string
    {
        $model = is_string($model) ? trim($model) : '';
        if ($model === '') {
            return self::DEFAULT_MODEL;
        }

        $model = preg_replace('#^models/#', '', $model) ?? $model;
        return trim($model) !== '' ? trim($model) : self::DEFAULT_MODEL;
    }

    private function modelFallbacks(string $model): array
    {
        $model = trim($model);
        $fallbacks = [$model];

        $familyFallbacks = null;
        if (str_starts_with($model, 'gemini-2.0-flash')) {
            $familyFallbacks = ['gemini-2.0-flash', 'gemini-2.0-flash-latest', 'gemini-2.0-flash-001', 'gemini-2.0-flash-002'];
        } elseif (str_starts_with($model, 'gemini-2.0-pro')) {
            $familyFallbacks = ['gemini-2.0-pro', 'gemini-2.0-pro-latest', 'gemini-2.0-pro-001', 'gemini-2.0-pro-002'];
        } elseif (str_starts_with($model, 'gemini-1.5-flash')) {
            $familyFallbacks = ['gemini-1.5-flash-latest', 'gemini-1.5-flash', 'gemini-1.5-flash-001', 'gemini-1.5-flash-002'];
        } elseif (str_starts_with($model, 'gemini-1.5-pro')) {
            $familyFallbacks = ['gemini-1.5-pro-latest', 'gemini-1.5-pro', 'gemini-1.5-pro-001', 'gemini-1.5-pro-002'];
        } elseif (str_starts_with($model, 'gemini-1.0-pro') || str_starts_with($model, 'gemini-pro')) {
            $familyFallbacks = ['gemini-1.0-pro-latest', 'gemini-1.0-pro', 'gemini-pro', 'gemini-1.0-pro-001'];
        }

        if (is_array($familyFallbacks)) {
            $fallbacks = array_merge($fallbacks, $familyFallbacks);
        }

        $seen = [];
        $out = [];
        foreach ($fallbacks as $m) {
            $m = is_string($m) ? trim($m) : '';
            if ($m === '' || isset($seen[$m])) {
                continue;
            }
            $seen[$m] = true;
            $out[] = $m;
        }

        return $out;
    }

    private function postGenerateContent(string $apiKey, string $model, array $payload)
    {
        $versions = ['v1', 'v1beta'];
        $models = $this->modelFallbacks($model);

        $lastResponse = null;

        foreach ($versions as $version) {
            foreach ($models as $m) {
                $lastResponse = Http::timeout(30)
                    ->retry(2, 200, null, false)
                    ->withQueryParameters(['key' => $apiKey])
                    ->post('https://generativelanguage.googleapis.com/' . $version . '/models/' . $m . ':generateContent', $payload);

                if ($lastResponse->successful()) {
                    return $lastResponse;
                }

                $status = $lastResponse->status();
                if ($status === 401 || $status === 403) {
                    return $lastResponse;
                }

                if ($status !== 404) {
                    $body = $lastResponse->body();
                    if (!is_string($body) || !str_contains(strtolower($body), 'is not found')) {
                        break;
                    }
                }
            }
        }

        if ($lastResponse && $lastResponse->status() === 404) {
            foreach ($versions as $version) {
                $available = $this->listModels($apiKey, $version);
                $picked = $this->pickModelFromList($available);
                if (!is_string($picked) || trim($picked) === '') {
                    continue;
                }

                $lastResponse = Http::timeout(30)
                    ->retry(1, 200, null, false)
                    ->withQueryParameters(['key' => $apiKey])
                    ->post('https://generativelanguage.googleapis.com/' . $version . '/models/' . $picked . ':generateContent', $payload);

                if ($lastResponse->successful()) {
                    return $lastResponse;
                }
            }
        }

        return $lastResponse;
    }

    public function generateTemplateHtml(string $apiKey, string $prompt, int $maxOutputTokens = 1200, ?string $model = null): array
    {
        $prompt = trim($prompt);
        $model = $this->normalizeModel($model);

        try {
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => "Generate an email template as valid HTML only (no markdown, no code fences). Do NOT include data:image base64 images; use only https:// image URLs.\n\n" . $prompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => max(1, $maxOutputTokens),
                ],
            ];

            $response = $this->postGenerateContent($apiKey, $model, $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'html' => null,
                    'tokens' => null,
                    'message' => $this->describeFailure($response),
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
                    'html' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned invalid JSON.',
                    'raw' => ['body' => $response->body()],
                ];
            }

            $parts = $data['candidates'][0]['content']['parts'] ?? null;
            $html = null;
            if (is_array($parts)) {
                $texts = [];
                foreach ($parts as $p) {
                    if (is_array($p) && is_string($p['text'] ?? null)) {
                        $texts[] = $p['text'];
                    }
                }
                $html = trim(implode("\n", $texts));
            }

            if (!is_string($html) || $html === '') {
                return [
                    'success' => false,
                    'html' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned an empty response.',
                    'raw' => $data,
                ];
            }

            $html = preg_replace('/^```(?:html)?\s*/i', '', $html) ?? $html;
            $html = preg_replace('/\s*```\s*$/', '', $html) ?? $html;
            $html = trim($html);

            $tokens = null;
            $usage = $data['usageMetadata'] ?? null;
            if (is_array($usage) && is_numeric($usage['totalTokenCount'] ?? null)) {
                $tokens = (int) $usage['totalTokenCount'];
            }

            return [
                'success' => true,
                'html' => $html,
                'tokens' => $tokens,
                'message' => null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'html' => null,
                'tokens' => null,
                'message' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    public function generateTemplateUnlayerDesign(string $apiKey, string $prompt, int $maxOutputTokens = 1200, ?string $model = null): array
    {
        $prompt = trim($prompt);
        $model = $this->normalizeModel($model);

        try {
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => "Generate an email template as Unlayer design JSON. Output ONLY valid JSON (no markdown, no code fences). The JSON must be a design object with keys: counters and body. The body must contain rows/columns/contents with proper Unlayer block types like heading, text, image, button, divider, spacer. Do NOT include data:image base64 images; use only https:// image URLs.\n\n" . $prompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.4,
                    'maxOutputTokens' => max(1, $maxOutputTokens),
                ],
            ];

            $response = $this->postGenerateContent($apiKey, $model, $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'design' => null,
                    'tokens' => null,
                    'message' => $this->describeFailure($response),
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
                    'design' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned invalid JSON.',
                    'raw' => ['body' => $response->body()],
                ];
            }

            $parts = $data['candidates'][0]['content']['parts'] ?? null;
            $text = null;
            if (is_array($parts)) {
                $texts = [];
                foreach ($parts as $p) {
                    if (is_array($p) && is_string($p['text'] ?? null)) {
                        $texts[] = $p['text'];
                    }
                }
                $text = trim(implode("\n", $texts));
            }

            if (!is_string($text) || $text === '') {
                return [
                    'success' => false,
                    'design' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned an empty response.',
                    'raw' => $data,
                ];
            }

            $text = preg_replace('/^```(?:json)?\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```\s*$/', '', $text) ?? $text;
            $text = trim($text);

            $decoded = json_decode($text, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return [
                    'success' => false,
                    'design' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned invalid Unlayer design JSON.',
                    'raw' => $data,
                ];
            }

            $design = $decoded;
            if (!isset($design['body']) && (isset($design['rows']) || isset($design['values']))) {
                $design = [
                    'counters' => [],
                    'body' => $decoded,
                ];
            }

            if (!is_array($design['body'] ?? null)) {
                return [
                    'success' => false,
                    'design' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned an invalid Unlayer design structure.',
                    'raw' => $data,
                ];
            }

            if (!isset($design['counters']) || (!is_array($design['counters']) && !is_object($design['counters']))) {
                $design['counters'] = [];
            }

            $tokens = null;
            $usage = $data['usageMetadata'] ?? null;
            if (is_array($usage) && is_numeric($usage['totalTokenCount'] ?? null)) {
                $tokens = (int) $usage['totalTokenCount'];
            }

            return [
                'success' => true,
                'design' => $design,
                'tokens' => $tokens,
                'message' => null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'design' => null,
                'tokens' => null,
                'message' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }

    public function generateText(string $apiKey, string $prompt, int $maxOutputTokens = 1200, ?string $model = null): array
    {
        $prompt = trim($prompt);
        $model = $this->normalizeModel($model);

        try {
            $payload = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            [
                                'text' => "Write email copy as plain text only (no markdown, no code fences).\n\n" . $prompt,
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => max(1, $maxOutputTokens),
                ],
            ];

            $response = $this->postGenerateContent($apiKey, $model, $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'text' => null,
                    'tokens' => null,
                    'message' => $this->describeFailure($response),
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
                    'text' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned invalid JSON.',
                    'raw' => ['body' => $response->body()],
                ];
            }

            $parts = $data['candidates'][0]['content']['parts'] ?? null;
            $text = null;
            if (is_array($parts)) {
                $texts = [];
                foreach ($parts as $p) {
                    if (is_array($p) && is_string($p['text'] ?? null)) {
                        $texts[] = $p['text'];
                    }
                }
                $text = trim(implode("\n", $texts));
            }

            if (!is_string($text) || $text === '') {
                return [
                    'success' => false,
                    'text' => null,
                    'tokens' => null,
                    'message' => 'Gemini returned an empty response.',
                    'raw' => $data,
                ];
            }

            $text = preg_replace('/^```\s*/i', '', $text) ?? $text;
            $text = preg_replace('/\s*```\s*$/', '', $text) ?? $text;
            $text = trim($text);

            $tokens = null;
            $usage = $data['usageMetadata'] ?? null;
            if (is_array($usage) && is_numeric($usage['totalTokenCount'] ?? null)) {
                $tokens = (int) $usage['totalTokenCount'];
            }

            return [
                'success' => true,
                'text' => $text,
                'tokens' => $tokens,
                'message' => null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'text' => null,
                'tokens' => null,
                'message' => $e->getMessage(),
                'raw' => null,
            ];
        }
    }
}
