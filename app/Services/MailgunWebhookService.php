<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailgunWebhookService
{
    /**
     * Create or update Mailgun webhook for a domain.
     */
    public function createOrUpdateWebhook(
        string $domain,
        string $apiKey,
        string $webhookId,
        string $webhookUrl,
        array $events = ['bounced', 'failed', 'complained', 'delivered']
    ): array {
        try {
            $response = Http::withBasicAuth('api', $apiKey)
                ->asMultipart()
                ->post("https://api.mailgun.net/v3/domains/{$domain}/webhooks", [
                    'id' => $webhookId,
                    'url' => $webhookUrl,
                ]);

            if ($response->successful()) {
                Log::info("Mailgun webhook created/updated successfully", [
                    'domain' => $domain,
                    'webhook_id' => $webhookId,
                    'url' => $webhookUrl,
                ]);

                return [
                    'success' => true,
                    'webhook_id' => $webhookId,
                    'url' => $webhookUrl,
                    'response' => $response->json(),
                ];
            }

            // If webhook already exists, try to update it
            if ($response->status() === 400) {
                return $this->updateWebhook($domain, $apiKey, $webhookId, $webhookUrl);
            }

            Log::error("Failed to create Mailgun webhook", [
                'domain' => $domain,
                'webhook_id' => $webhookId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("Exception creating Mailgun webhook: " . $e->getMessage(), [
                'domain' => $domain,
                'webhook_id' => $webhookId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update existing Mailgun webhook.
     */
    public function updateWebhook(
        string $domain,
        string $apiKey,
        string $webhookId,
        string $webhookUrl
    ): array {
        try {
            $response = Http::withBasicAuth('api', $apiKey)
                ->asMultipart()
                ->put("https://api.mailgun.net/v3/domains/{$domain}/webhooks/{$webhookId}", [
                    'url' => $webhookUrl,
                ]);

            if ($response->successful()) {
                Log::info("Mailgun webhook updated successfully", [
                    'domain' => $domain,
                    'webhook_id' => $webhookId,
                    'url' => $webhookUrl,
                ]);

                return [
                    'success' => true,
                    'webhook_id' => $webhookId,
                    'url' => $webhookUrl,
                    'response' => $response->json(),
                ];
            }

            Log::error("Failed to update Mailgun webhook", [
                'domain' => $domain,
                'webhook_id' => $webhookId,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error("Exception updating Mailgun webhook: " . $e->getMessage(), [
                'domain' => $domain,
                'webhook_id' => $webhookId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete Mailgun webhook.
     */
    public function deleteWebhook(
        string $domain,
        string $apiKey,
        string $webhookId
    ): bool {
        try {
            $response = Http::withBasicAuth('api', $apiKey)
                ->delete("https://api.mailgun.net/v3/domains/{$domain}/webhooks/{$webhookId}");

            if ($response->successful()) {
                Log::info("Mailgun webhook deleted successfully", [
                    'domain' => $domain,
                    'webhook_id' => $webhookId,
                ]);

                return true;
            }

            Log::warning("Failed to delete Mailgun webhook", [
                'domain' => $domain,
                'webhook_id' => $webhookId,
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Exception deleting Mailgun webhook: " . $e->getMessage(), [
                'domain' => $domain,
                'webhook_id' => $webhookId,
            ]);

            return false;
        }
    }

    /**
     * Get all webhooks for a domain.
     */
    public function getWebhooks(string $domain, string $apiKey): array
    {
        try {
            $response = Http::withBasicAuth('api', $apiKey)
                ->get("https://api.mailgun.net/v3/domains/{$domain}/webhooks");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'webhooks' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error("Exception getting Mailgun webhooks: " . $e->getMessage(), [
                'domain' => $domain,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get webhook URL for the application.
     */
    public function getWebhookUrl(): string
    {
        $baseUrl = config('app.url');
        return rtrim($baseUrl, '/') . '/webhooks/mailgun';
    }
}



