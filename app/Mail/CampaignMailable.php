<?php

namespace App\Mail;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\PersonalizationService;
use App\Services\ReplyTrackingAddressService;
use App\Services\SpintaxService;
use App\Services\SpamScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CampaignMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Campaign $campaign,
        public CampaignRecipient $recipient
    ) {
        // Set Return-Path header using withSymfonyMessage
        $this->setReturnPathHeader();
        $this->setTrackingHeaders();
        
        // Process spintax for this recipient
        $this->processSpintax();
    }
    
    /**
     * Set the Return-Path header to bounce server email.
     */
    protected function setReturnPathHeader(): void
    {
        $returnPath = $this->getReturnPathEmail();
        
        if ($returnPath) {
            $this->withSymfonyMessage(function ($message) use ($returnPath) {
                // Use returnPath() method on Symfony Email object
                $message->returnPath($returnPath);
            });
            
            Log::info('Set Return-Path header in campaign email', [
                'campaign_id' => $this->campaign->id,
                'recipient_email' => $this->recipient->email ?? 'unknown',
                'return_path' => $returnPath,
            ]);
        }
    }

    protected function setTrackingHeaders(): void
    {
        $campaignId = $this->campaign->id;
        $listId = $this->campaign->list_id;
        $recipientUuid = $this->recipient->uuid;

        $this->withSymfonyMessage(function ($message) use ($campaignId, $listId, $recipientUuid) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('X-Campaign-ID', (string) $campaignId);
            if (!empty($listId)) {
                $headers->addTextHeader('X-List-ID', (string) $listId);
            }
            $headers->addTextHeader('X-Recipient-UUID', (string) $recipientUuid);
            
            // Set custom Message-ID with UUID for better reply tracking
            $domain = parse_url(config('app.url'), PHP_URL_HOST) ?? 'mailzen.invalid';
            $messageId = "{$recipientUuid}@{$domain}";
            $headers->addIdHeader('Message-ID', $messageId);
        });

        Log::debug('Added campaign tracking headers to email', [
            'campaign_id' => $campaignId,
            'list_id' => $listId,
            'recipient_id' => $this->recipient->id,
            'recipient_uuid' => $recipientUuid,
            'message_id' => "{$recipientUuid}@" . (parse_url(config('app.url'), PHP_URL_HOST) ?? 'mailzen.invalid'),
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromEmail = $this->getFromEmail();
        $fromName = $this->getFromName();
        
        // Ensure fromEmail is a string (handle case where it might be an array)
        if (is_array($fromEmail)) {
            $fromEmail = $fromEmail['address'] ?? $fromEmail[0] ?? (string) reset($fromEmail);
        }
        $fromEmail = (string) $fromEmail;
        
        // Ensure fromName is a string if provided
        if ($fromName && is_array($fromName)) {
            $fromName = $fromName['name'] ?? $fromName[0] ?? (string) reset($fromName);
        }
        $fromName = $fromName ? (string) $fromName : null;
        
        // Create Address object if name is provided, otherwise use string
        $from = $fromName 
            ? new Address($fromEmail, $fromName)
            : $fromEmail;

        $replyTo = app(ReplyTrackingAddressService::class)
            ->effectiveReplyTo($this->campaign, $this->recipient, $fromEmail);

        // Log bounce server username for each email being sent
        $this->logBounceServerInfo();
        
        return new Envelope(
            subject: $this->getProcessedSubject(),
            from: $from,
            replyTo: $replyTo !== '' ? $replyTo : null,
        );
    }
    
    /**
     * Process spintax for this recipient.
     */
    protected function processSpintax(): void
    {
        $spintaxService = app(SpintaxService::class);
        
        // Process subject
        if ($spintaxService->hasSpintax($this->campaign->subject)) {
            $this->campaign->subject = $spintaxService->spin($this->campaign->subject);
        }
        
        // Process HTML content
        if ($spintaxService->hasSpintax($this->campaign->html_content)) {
            $this->campaign->html_content = $spintaxService->spin($this->campaign->html_content);
        }
        
        // Process plain text content
        if ($spintaxService->hasSpintax($this->campaign->plain_text_content)) {
            $this->campaign->plain_text_content = $spintaxService->spin($this->campaign->plain_text_content);
        }
    }
    
    /**
     * Get processed subject (after spintax and personalization).
     */
    protected function getProcessedSubject(): string
    {
        return $this->personalizeContent($this->campaign->subject);
    }
    
    /**
     * Log bounce server information for this email.
     */
    protected function logBounceServerInfo(): void
    {
        try {
            // Load delivery server with bounce server relationship
            $this->campaign->loadMissing('deliveryServer.bounceServer');
            
            $deliveryServer = $this->campaign->deliveryServer;
            $bounceServer = $deliveryServer?->bounceServer;
            
            Log::info('Sending campaign email with bounce server info', [
                'campaign_id' => $this->campaign->id,
                'recipient_email' => $this->recipient->email,
                'recipient_id' => $this->recipient->id,
                'delivery_server_id' => $deliveryServer?->id,
                'delivery_server_name' => $deliveryServer?->name,
                'bounce_server_id' => $bounceServer?->id,
                'bounce_server_username' => $bounceServer?->username,
                'bounce_server_email' => $bounceServer?->username, // Alias for clarity
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail email sending
            Log::warning('Failed to log bounce server info', [
                'campaign_id' => $this->campaign->id,
                'recipient_email' => $this->recipient->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the from email address.
     */
    protected function getFromEmail(): string
    {
        $this->campaign->loadMissing('deliveryServer', 'emailList');

        $campaignFrom = $this->normalizeEmail($this->campaign->from_email ?? null);
        if ($campaignFrom !== '') {
            return $campaignFrom;
        }

        $serverFrom = $this->normalizeEmail($this->campaign->deliveryServer?->from_email ?? null);
        if ($serverFrom !== '') {
            return $serverFrom;
        }

        $listFrom = $this->normalizeEmail($this->campaign->emailList?->from_email ?? null);
        if ($listFrom !== '') {
            return $listFrom;
        }

        return $this->getDefaultFromEmail();
    }

    /**
     * Get the from name, with fallbacks.
     */
    protected function getFromName(): ?string
    {
        $this->campaign->loadMissing('deliveryServer');

        $campaignName = $this->normalizeName($this->campaign->from_name ?? null);
        if ($campaignName !== '') {
            return $campaignName;
        }

        $serverName = $this->normalizeName($this->campaign->deliveryServer?->from_name ?? null);
        if ($serverName !== '') {
            return $serverName;
        }

        return null;
    }

    protected function normalizeEmail(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value['address'] ?? $value[0] ?? (string) reset($value);
        }

        $email = trim((string) ($value ?? ''));

        return $email;
    }

    protected function normalizeName(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value['name'] ?? $value[0] ?? (string) reset($value);
        }

        return trim((string) ($value ?? ''));
    }
    
    /**
     * Get the default from email from config, ensuring it's a string.
     */
    protected function getDefaultFromEmail(): string
    {
        $mailFrom = config('mail.from');
        
        // Handle different config structures
        if (is_array($mailFrom)) {
            return $mailFrom['address'] ?? $mailFrom[0] ?? 'noreply@example.com';
        }
        
        // If config('mail.from.address') exists, use it
        $mailFromAddress = config('mail.from.address');
        if ($mailFromAddress && !is_array($mailFromAddress)) {
            return (string) $mailFromAddress;
        }
        
        // Final fallback
        return 'noreply@example.com';
    }
    
    /**
     * Get the Return-Path email from bounce server.
     */
    protected function getReturnPathEmail(): ?string
    {
        try {
            // Prefer campaign-level bounce server (independent of delivery server)
            $this->campaign->loadMissing('bounceServer');

            $bounceServer = $this->campaign->bounceServer;
            if ($bounceServer && !empty($bounceServer->username)) {
                return $bounceServer->username;
            }

            // Backward-compatible fallback: delivery server bounce server
            $this->campaign->loadMissing('deliveryServer.bounceServer');
            $deliveryServer = $this->campaign->deliveryServer;
            $fallbackBounce = $deliveryServer?->bounceServer;

            if ($fallbackBounce && !empty($fallbackBounce->username)) {
                return $fallbackBounce->username;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to get Return-Path email from bounce server', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $htmlContent = $this->prepareHtmlContent();
        $plainTextContent = $this->preparePlainTextContent();

        return new Content(
            html: 'emails.campaign',
            text: 'emails.campaign-text',
            with: [
                'campaign' => $this->campaign,
                'recipient' => $this->recipient,
                'htmlContent' => $htmlContent,
                'plainTextContent' => $plainTextContent,
                'trackOpenUrl' => $this->getTrackOpenUrl(),
                'unsubscribeUrl' => $this->getUnsubscribeUrl(),
            ],
        );
    }

    /**
     * Prepare HTML content with tracking and personalization.
     */
    protected function prepareHtmlContent(): string
    {
        $content = $this->campaign->html_content ?? '';
        
        // Replace unsubscribe URL placeholders first (before personalization)
        if (stripos($content, '{unsubscribe_url}') !== false) {
            $content = str_ireplace('{unsubscribe_url}', $this->getUnsubscribeUrl(), $content);
        }
        if (stripos($content, '{{unsubscribe_url}}') !== false) {
            $content = str_ireplace('{{unsubscribe_url}}', $this->getUnsubscribeUrl(), $content);
        }
        
        // Now personalize the content
        $content = $this->personalizeContent($content);

        // Append footer template if configured
        $footerTemplateId = $this->campaign->settings['footer_template_id'] ?? null;
        if ($footerTemplateId) {
            $footerTemplate = \App\Models\Template::find($footerTemplateId);
            if ($footerTemplate && $footerTemplate->type === 'footer') {
                $footerContent = $this->personalizeContent($footerTemplate->html_content ?? '');
                // Insert before closing body tag
                if (strpos($content, '</body>') !== false) {
                    $content = str_replace('</body>', $footerContent . '</body>', $content);
                } else {
                    $content .= $footerContent;
                }
            }
        }

        // Append signature template if configured
        $signatureTemplateId = $this->campaign->settings['signature_template_id'] ?? null;
        if ($signatureTemplateId) {
            $signatureTemplate = \App\Models\Template::find($signatureTemplateId);
            if ($signatureTemplate && $signatureTemplate->type === 'signature') {
                $signatureContent = $this->personalizeContent($signatureTemplate->html_content ?? '');
                // Insert before closing body tag (after footer if present)
                if (strpos($content, '</body>') !== false) {
                    $content = str_replace('</body>', $signatureContent . '</body>', $content);
                } else {
                    $content .= $signatureContent;
                }
            }
        }

        // Add tracking pixel if enabled
        if ($this->campaign->track_opens) {
            $trackPixelUrl = $this->getTrackOpenUrl();
            $trackPixel = '<img src="' . $trackPixelUrl . '" width="1" height="1" style="display:none;" alt="" />';

            $fallbackPixelUrl = $this->getTrackOpenUrlOnMainDomain();
            $trackHost = parse_url($trackPixelUrl, PHP_URL_HOST);
            $fallbackHost = parse_url($fallbackPixelUrl, PHP_URL_HOST);
            if (is_string($trackHost) && $trackHost !== '' && is_string($fallbackHost) && $fallbackHost !== '' && strcasecmp($trackHost, $fallbackHost) !== 0) {
                if ($this->getTrackingBaseUrl()) {
                    $trackPixel .= '<img src="' . $fallbackPixelUrl . '" width="1" height="1" style="display:none;" alt="" />';
                }
            }
            $content = str_replace('</body>', $trackPixel . '</body>', $content);
            if (strpos($content, '</body>') === false) {
                $content .= $trackPixel;
            }
        }

        // Wrap links with click tracking if enabled
        if ($this->campaign->track_clicks) {
            $content = $this->wrapLinksWithTracking($content);
        }

        // Add unsubscribe link (only if no unsubscribe URL placeholder was found)
        $unsubscribeLink = '<p style="font-size: 12px; color: #999; margin-top: 20px;">
            <a href="' . $this->getUnsubscribeUrl() . '" style="color: #999;">Unsubscribe</a>
        </p>';
        
        // Check if unsubscribe URL was already handled in content
        if (strpos($content, $this->getUnsubscribeUrl()) === false) {
            // Add unsubscribe link before closing body tag, or at the end if no body tag
            $content = str_replace('</body>', $unsubscribeLink . '</body>', $content);
            if (strpos($content, '</body>') === false) {
                $content .= $unsubscribeLink;
            }
        }

        return $content;
    }

    /**
     * Prepare plain text content with personalization.
     */
    protected function preparePlainTextContent(): string
    {
        $content = $this->campaign->plain_text_content ?? strip_tags($this->campaign->html_content ?? '');
        
        // Replace unsubscribe URL placeholders first (before personalization)
        if (strpos($content, '{unsubscribe_url}') !== false) {
            $content = str_replace('{unsubscribe_url}', $this->getUnsubscribeUrl(), $content);
        }
        if (strpos($content, '{{unsubscribe_url}}') !== false) {
            $content = str_replace('{{unsubscribe_url}}', $this->getUnsubscribeUrl(), $content);
        }
        
        // Now personalize the content
        $content = $this->personalizeContent($content);
        
        // Append footer template to plain text if configured
        $footerTemplateId = $this->campaign->settings['footer_template_id'] ?? null;
        if ($footerTemplateId) {
            $footerTemplate = \App\Models\Template::find($footerTemplateId);
            if ($footerTemplate && $footerTemplate->type === 'footer') {
                $footerText = $this->personalizeContent($footerTemplate->plain_text_content ?? '');
                $content .= "\n\n" . $footerText;
            }
        }

        // Append signature template to plain text if configured
        $signatureTemplateId = $this->campaign->settings['signature_template_id'] ?? null;
        if ($signatureTemplateId) {
            $signatureTemplate = \App\Models\Template::find($signatureTemplateId);
            if ($signatureTemplate && $signatureTemplate->type === 'signature') {
                $signatureText = $this->personalizeContent($signatureTemplate->plain_text_content ?? '');
                $content .= "\n\n" . $signatureText;
            }
        }
        
        // Add unsubscribe link to plain text (only if no unsubscribe URL placeholder was found)
        if (strpos($content, $this->getUnsubscribeUrl()) === false) {
            $content .= "\n\n---\nUnsubscribe: " . $this->getUnsubscribeUrl();
        }

        return $content;
    }

    /**
     * Personalize content with recipient data.
     */
    protected function personalizeContent(string $content): string
    {
        return app(PersonalizationService::class)->personalizeForCampaignRecipient($content, $this->recipient);
    }

    /**
     * Wrap links with click tracking.
     */
    protected function wrapLinksWithTracking(string $content): string
    {
        // Match all <a href="..."> tags
        return preg_replace_callback(
            '/<a\s+([^>]*\s+)?href=["\']([^"\']+)["\']([^>]*)>/i',
            function ($matches) {
                $originalUrl = $matches[2];

                if ($this->isAlreadyTrackingUrl($originalUrl)) {
                    return $matches[0];
                }
                $trackUrl = $this->getTrackClickUrl($originalUrl);
                
                // Preserve existing attributes
                $attributes = $matches[1] . $matches[3];
                
                return '<a ' . trim($attributes) . ' href="' . $trackUrl . '">';
            },
            $content
        );
    }

    protected function isAlreadyTrackingUrl(string $url): bool
    {
        if (str_contains($url, '/track/click/') || str_contains($url, '/t/click/')) {
            return true;
        }

        $trackingBaseUrl = $this->getTrackingBaseUrl();
        if (!$trackingBaseUrl) {
            return false;
        }

        $trackingHost = parse_url($trackingBaseUrl, PHP_URL_HOST);
        $host = parse_url($url, PHP_URL_HOST);

        if (!is_string($trackingHost) || $trackingHost === '' || !is_string($host) || $host === '') {
            return false;
        }

        if (strcasecmp($trackingHost, $host) !== 0) {
            return false;
        }

        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');

        return str_starts_with($path, '/track/click/') || str_starts_with($path, '/t/click/');
    }

    protected function getTrackingBaseUrl(): ?string
    {
        $this->campaign->loadMissing('trackingDomain');

        if (!$this->campaign->trackingDomain || !$this->campaign->trackingDomain->isVerified()) {
            return null;
        }

        return 'https://' . $this->campaign->trackingDomain->domain;
    }

    protected function trackingRoute(string $name, array $parameters = []): string
    {
        $baseUrl = $this->getTrackingBaseUrl();
        if (!$baseUrl) {
            return route($name, $parameters);
        }

        $path = route($name, $parameters, false);

        return rtrim($baseUrl, '/') . $path;
    }

    /**
     * Get track open URL.
     */
    protected function getTrackOpenUrl(): string
    {
        // Use legacy tracking route which expects the recipient UUID
        // The v2 route requires hashed campaign/subscriber ids that are not available here
        return $this->trackingRoute('track.open.legacy', ['uuid' => $this->recipient->uuid]);
    }

    protected function getTrackOpenUrlOnMainDomain(): string
    {
        return route('track.open.legacy', ['uuid' => $this->recipient->uuid]);
    }

    /**
     * Get track click URL.
     */
    protected function getTrackClickUrl(string $originalUrl): string
    {
        // Use legacy click tracking route that accepts recipient UUID + encoded URL
        $encodedUrl = rtrim(strtr(base64_encode($originalUrl), '+/', '-_'), '=');
        return $this->trackingRoute('track.click.legacy', [
            'uuid' => $this->recipient->uuid,
            'url' => $encodedUrl,
        ]);
    }

    /**
     * Get unsubscribe URL.
     */
    protected function getUnsubscribeUrl(): string
    {
        return $this->trackingRoute('unsubscribe', ['uuid' => $this->recipient->uuid]);
    }
}

