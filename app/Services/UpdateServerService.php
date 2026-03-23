<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class UpdateServerService
{
    private function apiRoot(string $baseUrl): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');

        if (str_contains($baseUrl, '/wp-json/')) {
            return $baseUrl;
        }

        return $baseUrl . '/wp-json/v1';
    }

    private function legacyApiRoot(string $baseUrl): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');

        if (str_contains($baseUrl, '/wp-json/')) {
            return $baseUrl;
        }

        return $baseUrl . '/wp-json/lss-bridge/v1';
    }

    private function shouldFallbackToLegacy(?int $status, mixed $json): bool
    {
        if ($status !== 404) {
            return false;
        }

        if (!is_array($json)) {
            return false;
        }

        return (string) ($json['code'] ?? '') === 'rest_no_route';
    }

    private function licenseUrl(string $action): ?string
    {
        $url = config('services.update_server.license.' . $action);
        if (!is_string($url) || trim($url) === '') {
            return null;
        }

        return $url;
    }

    private function isLicenseValidFromData(?array $data): bool
    {
        if (!is_array($data)) {
            return false;
        }

        if (($data['success'] ?? null) === true) {
            return true;
        }

        if (($data['valid'] ?? null) === true) {
            return true;
        }

        $status = $data['status'] ?? null;
        if (is_string($status) && in_array(strtolower(trim($status)), ['success', 'active', 'activated', 'valid'], true)) {
            return true;
        }

        return false;
    }

    private function licenseRequest(string $action, array $payload): array
    {
        $url = $this->licenseUrl($action);

        if (!$url) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'License endpoint is not configured.',
                'status' => null,
                'valid' => false,
            ];
        }

        try {
            $licenseKey = $payload['license_key'] ?? null;
            $licenseKey = is_string($licenseKey) ? trim($licenseKey) : $licenseKey;

            if ($action === 'deactivate' && is_string($licenseKey) && $licenseKey !== '' && is_string($url) && trim($url) !== '') {
                $trimmedUrl = rtrim($url, '/');
                if (!str_contains($trimmedUrl, '{license_key}') && !str_ends_with($trimmedUrl, '/' . $licenseKey)) {
                    $url = $trimmedUrl . '/' . rawurlencode($licenseKey);
                }
            }

            $payloadToSend = $payload;
            if (is_string($licenseKey) && $licenseKey !== '') {
                $payloadToSend['license_key'] = $licenseKey;
                $payloadToSend['purchase_code'] = $payloadToSend['purchase_code'] ?? $licenseKey;
                $payloadToSend['purchaseCode'] = $payloadToSend['purchaseCode'] ?? $licenseKey;
                $payloadToSend['code'] = $payloadToSend['code'] ?? $licenseKey;
                $payloadToSend['envato_purchase_code'] = $payloadToSend['envato_purchase_code'] ?? $licenseKey;
            }

            $request = Http::timeout(20)
                ->retry(2, 200, null, false)
                ->acceptJson()
                ->asJson();

            if (is_string($licenseKey) && $licenseKey !== '') {
                $request = $request->withHeaders([
                    'Authorization' => $licenseKey,
                    'X-Purchase-Code' => $licenseKey,
                    'X-License-Key' => $licenseKey,
                    'X-Envato-Purchase-Code' => $licenseKey,
                ]);
            }

            $response = $request->post($url, $payloadToSend);

            if (!$response->successful()) {
                $message = 'License server request failed.';
                $message .= ' URL: ' . $url;
                $message .= ' (HTTP ' . $response->status() . ')';

                $body = $response->body();
                if (is_string($body) && trim($body) !== '') {
                    $message .= ': ' . $body;
                }

                return [
                    'success' => false,
                    'data' => null,
                    'message' => $message,
                    'status' => $response->status(),
                    'valid' => false,
                    'raw' => $response->body(),
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'License server returned invalid JSON.',
                    'status' => $response->status(),
                    'valid' => false,
                    'raw' => $response->body(),
                ];
            }

            $valid = $this->isLicenseValidFromData($data);
            $message = is_string($data['message'] ?? null) ? $data['message'] : null;

            return [
                'success' => true,
                'data' => $data,
                'message' => $message,
                'status' => $response->status(),
                'valid' => $valid,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'status' => null,
                'valid' => false,
            ];
        }
    }

    public function licenseActivate(string $licenseKey, string $domain, string $productSecret, string $productName): array
    {
        return $this->licenseRequest('activate', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'product_secret' => $productSecret,
            'name' => $productName,
        ]);
    }

    public function licenseCheck(string $licenseKey, string $domain, string $productSecret, string $productName): array
    {
        return $this->licenseRequest('check', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'product_secret' => $productSecret,
            'name' => $productName,
        ]);
    }

    public function licenseVerify(string $licenseKey, string $domain, string $productSecret, string $productName): array
    {
        return $this->licenseRequest('verify', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'product_secret' => $productSecret,
            'name' => $productName,
        ]);
    }

    public function licenseDeactivate(string $licenseKey, string $domain, string $productSecret, string $productName): array
    {
        return $this->licenseRequest('deactivate', [
            'license_key' => $licenseKey,
            'domain' => $domain,
            'product_secret' => $productSecret,
            'name' => $productName,
        ]);
    }

    public function getProductsByLicense(string $baseUrl, string $licenseKey, string $domain, bool $refresh = false): array
    {
        $cacheKey = 'update_server:products_by_license:' . md5($baseUrl) . ':' . md5($licenseKey) . ':' . md5($domain);

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($baseUrl, $licenseKey, $domain) {
            try {
                $url = $this->apiRoot($baseUrl) . '/products/by-license';
                $usedUrl = $url;

                $response = Http::timeout(15)
                    ->retry(2, 200, null, false)
                    ->acceptJson()
                    ->withHeaders([
                        'Authorization' => $licenseKey,
                    ])
                    ->get($url, [
                        'license_key' => $licenseKey,
                        'domain' => $domain,
                    ]);

                if ($this->shouldFallbackToLegacy($response->status(), $response->json())) {
                    $legacyUrl = $this->legacyApiRoot($baseUrl) . '/products/by-license';
                    if ($legacyUrl !== $url) {
                        $usedUrl = $legacyUrl;
                        $response = Http::timeout(15)
                            ->retry(2, 200, null, false)
                            ->acceptJson()
                            ->withHeaders([
                                'Authorization' => $licenseKey,
                            ])
                            ->get($legacyUrl, [
                                'license_key' => $licenseKey,
                                'domain' => $domain,
                            ]);
                    }
                }

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Update server request failed. URL: ' . $usedUrl,
                        'status' => $response->status(),
                    ];
                }

                $data = $response->json();
                if (!is_array($data)) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Update server returned invalid JSON.',
                        'status' => $response->status(),
                    ];
                }

                $products = $data['products'] ?? null;
                if (!is_array($products)) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'No products returned for this license key. URL: ' . $usedUrl,
                        'status' => $response->status(),
                    ];
                }

                return [
                    'success' => true,
                    'data' => $products,
                    'message' => null,
                    'status' => $response->status(),
                ];
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => $e->getMessage(),
                    'status' => null,
                ];
            }
        });
    }

    public function getPrimaryProductIdByLicense(string $baseUrl, string $licenseKey, string $domain, bool $refresh = false): array
    {
        $productsResult = $this->getProductsByLicense($baseUrl, $licenseKey, $domain, $refresh);

        if (!($productsResult['success'] ?? false)) {
            return [
                'success' => false,
                'product_id' => null,
                'message' => $productsResult['message'] ?? 'Unable to fetch products for this license.',
                'status' => $productsResult['status'] ?? null,
                'raw' => $productsResult,
            ];
        }

        $products = $productsResult['data'] ?? null;
        if (!is_array($products) || empty($products)) {
            return [
                'success' => false,
                'product_id' => null,
                'message' => 'No products found for this license key.',
                'status' => $productsResult['status'] ?? null,
                'raw' => $productsResult,
            ];
        }

        $first = $products[0] ?? null;
        $productId = is_array($first) ? ($first['id'] ?? null) : null;

        if (!is_numeric($productId) || (int) $productId <= 0) {
            return [
                'success' => false,
                'product_id' => null,
                'message' => 'Product ID not available for this license key.',
                'status' => $productsResult['status'] ?? null,
                'raw' => $productsResult,
            ];
        }

        return [
            'success' => true,
            'product_id' => (int) $productId,
            'message' => null,
            'status' => $productsResult['status'] ?? null,
            'raw' => $productsResult,
        ];
    }

    public function getPrimaryProductByLicense(string $baseUrl, string $licenseKey, string $domain, bool $refresh = false): array
    {
        $productsResult = $this->getProductsByLicense($baseUrl, $licenseKey, $domain, $refresh);

        if (!($productsResult['success'] ?? false)) {
            return [
                'success' => false,
                'product' => null,
                'message' => $productsResult['message'] ?? 'Unable to fetch products for this license.',
                'status' => $productsResult['status'] ?? null,
                'raw' => $productsResult,
            ];
        }

        $products = $productsResult['data'] ?? null;
        if (!is_array($products) || empty($products)) {
            return [
                'success' => false,
                'product' => null,
                'message' => 'No products found for this license key.',
                'status' => $productsResult['status'] ?? null,
                'raw' => $productsResult,
            ];
        }

        $product = $products[0] ?? null;
        if (!is_array($product)) {
            return [
                'success' => false,
                'product' => null,
                'message' => 'Product not available for this license key.',
                'status' => $productsResult['status'] ?? null,
                'raw' => $productsResult,
            ];
        }

        return [
            'success' => true,
            'product' => $product,
            'message' => null,
            'status' => $productsResult['status'] ?? null,
            'raw' => $productsResult,
        ];
    }

    public function getProduct(int $productId, string $baseUrl, bool $refresh = false): array
    {
        $cacheKey = 'update_server:product:' . md5($baseUrl) . ':' . $productId;

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($productId, $baseUrl) {
            try {
                $url = $this->apiRoot($baseUrl) . '/products/' . $productId;
                $usedUrl = $url;

                $response = Http::timeout(15)
                    ->retry(2, 200, null, false)
                    ->acceptJson()
                    ->get($url);

                if ($response->status() === 404 && $this->shouldFallbackToLegacy($response->status(), $response->json())) {
                    $legacyUrl = $this->legacyApiRoot($baseUrl) . '/products/' . $productId;
                    if ($legacyUrl !== $url) {
                        $usedUrl = $legacyUrl;
                        $response = Http::timeout(15)
                            ->retry(2, 200, null, false)
                            ->acceptJson()
                            ->get($legacyUrl);
                    }
                }

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Update server request failed. URL: ' . $usedUrl,
                        'status' => $response->status(),
                    ];
                }

                $data = $response->json();
                if (!is_array($data)) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Update server returned invalid JSON.',
                        'status' => $response->status(),
                    ];
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => null,
                    'status' => $response->status(),
                ];
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => $e->getMessage(),
                    'status' => null,
                ];
            }
        });
    }

    public function getChangelogs(int $productId, string $baseUrl, bool $refresh = false): array
    {
        $cacheKey = 'update_server:changelogs:' . md5($baseUrl) . ':' . $productId;

        if ($refresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($productId, $baseUrl) {
            try {
                $url = $this->apiRoot($baseUrl) . '/changelogs/' . $productId;
                $usedUrl = $url;

                $response = Http::timeout(15)
                    ->retry(2, 200, null, false)
                    ->acceptJson()
                    ->get($url);

                if ($response->status() === 404 && $this->shouldFallbackToLegacy($response->status(), $response->json())) {
                    $legacyUrl = $this->legacyApiRoot($baseUrl) . '/changelogs/' . $productId;
                    if ($legacyUrl !== $url) {
                        $usedUrl = $legacyUrl;
                        $response = Http::timeout(15)
                            ->retry(2, 200, null, false)
                            ->acceptJson()
                            ->get($legacyUrl);
                    }
                }

                if (!$response->successful()) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Update server request failed. URL: ' . $usedUrl,
                        'status' => $response->status(),
                        'raw' => $response->body(),
                    ];
                }

                $data = $response->json();
                if (!is_array($data)) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => 'Update server returned invalid JSON.',
                        'status' => $response->status(),
                        'raw' => $response->body(),
                    ];
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => null,
                    'status' => $response->status(),
                ];
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => $e->getMessage(),
                    'status' => null,
                ];
            }
        });
    }

    public function requestDownloadUrl(
        string $baseUrl,
        string $licenseKey,
        string $domain,
        string $productSecret,
        string $productName
    ): array {
        try {
            $url = $this->apiRoot($baseUrl) . '/products/download';

            $response = Http::timeout(20)
                ->retry(2, 200, null, false)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'license_key' => $licenseKey,
                    'domain' => $domain,
                    'product_secret' => $productSecret,
                    'name' => $productName,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'download_url' => null,
                    'message' => 'Update server request failed. URL: ' . $url,
                    'status' => $response->status(),
                    'raw' => $response->body(),
                ];
            }

            $data = $response->json();
            if (!is_array($data)) {
                return [
                    'success' => false,
                    'download_url' => null,
                    'message' => 'Update server returned invalid JSON.',
                    'status' => $response->status(),
                    'raw' => $response->body(),
                ];
            }

            $downloadUrl = is_string($data['download_url'] ?? null) ? $data['download_url'] : null;
            if (!is_string($downloadUrl) || trim($downloadUrl) === '') {
                return [
                    'success' => false,
                    'download_url' => null,
                    'message' => 'Download URL not available.',
                    'status' => $response->status(),
                    'raw' => $data,
                ];
            }

            return [
                'success' => true,
                'download_url' => $downloadUrl,
                'message' => null,
                'status' => $response->status(),
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'download_url' => null,
                'message' => $e->getMessage(),
                'status' => null,
                'raw' => null,
            ];
        }
    }
}
