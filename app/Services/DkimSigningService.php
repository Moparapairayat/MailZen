<?php

namespace App\Services;

use App\Models\SendingDomain;
use App\Models\DeliveryServer;
use Illuminate\Support\Facades\Log;

class DkimSigningService
{
    /**
     * Check if a delivery server already signs emails with DKIM.
     */
    public function serverSignsWithDkim(DeliveryServer $server = null): bool
    {
        if (!$server) {
            return false;
        }

        // Check if server type typically signs with DKIM
        // API-based services (Mailgun, SendGrid, etc.) usually sign automatically
        $apiBasedServices = ['mailgun', 'sendgrid', 'postmark', 'amazon-ses', 'sparkpost'];
        
        if (in_array($server->type, $apiBasedServices)) {
            return true; // These services sign automatically
        }

        // Check server settings for DKIM signing flag
        $settings = $server->settings ?? [];
        return $settings['dkim_signing_enabled'] ?? false;
    }

    /**
     * Get sending domain for a given email address.
     */
    public function getSendingDomainForEmail(string $email, int $customerId = null): ?SendingDomain
    {
        // Extract domain from email
        $domain = substr(strrchr($email, '@'), 1);
        
        if (!$domain) {
            return null;
        }

        $query = SendingDomain::where('domain', $domain)
            ->where('status', 'verified');

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        return $query->first();
    }

    /**
     * Sign email content with DKIM signature.
     */
    public function signEmail(string $emailContent, SendingDomain $sendingDomain, string $fromEmail, string $subject): string
    {
        if (!$sendingDomain->dkim_private_key) {
            Log::warning('Sending domain has no DKIM private key', [
                'domain' => $sendingDomain->domain,
            ]);
            return $emailContent;
        }

        try {
            // Generate DKIM selector (typically 'mail' or 'default')
            $selector = 'mail';
            $domain = $sendingDomain->domain;

            // Parse email headers and body
            $parts = $this->parseEmail($emailContent);
            $headers = $parts['headers'];
            $body = $parts['body'];

            // Create DKIM signature
            $signature = $this->createDkimSignature(
                $headers,
                $body,
                $domain,
                $selector,
                $sendingDomain->dkim_private_key
            );

            // Add DKIM-Signature header
            $dkimHeader = "DKIM-Signature: v=1; a=rsa-sha256; c=relaxed/relaxed; d={$domain}; s={$selector}; " .
                         "h=from:to:subject:date:message-id; " .
                         "bh={$signature['body_hash']}; " .
                         "b={$signature['signature']}";

            // Insert DKIM header after other headers but before body
            $headerEnd = strpos($emailContent, "\r\n\r\n");
            if ($headerEnd !== false) {
                $emailContent = substr_replace($emailContent, "\r\n" . $dkimHeader, $headerEnd, 2);
            } else {
                // If no header/body separator, add headers at the beginning
                $emailContent = $dkimHeader . "\r\n" . $emailContent;
            }

            Log::info('DKIM signature added to email', [
                'domain' => $domain,
                'from' => $fromEmail,
            ]);

            return $emailContent;
        } catch (\Exception $e) {
            Log::error('Failed to sign email with DKIM', [
                'domain' => $sendingDomain->domain,
                'error' => $e->getMessage(),
            ]);
            return $emailContent; // Return unsigned email on error
        }
    }

    /**
     * Parse email into headers and body.
     */
    protected function parseEmail(string $emailContent): array
    {
        $parts = explode("\r\n\r\n", $emailContent, 2);
        
        if (count($parts) < 2) {
            $parts = explode("\n\n", $emailContent, 2);
        }

        return [
            'headers' => $parts[0] ?? '',
            'body' => $parts[1] ?? $emailContent,
        ];
    }

    /**
     * Create DKIM signature for email.
     */
    protected function createDkimSignature(string $headers, string $body, string $domain, string $selector, string $privateKey): array
    {
        // Canonicalize body (relaxed)
        $canonicalBody = $this->canonicalizeRelaxed($body);
        
        // Hash body
        $bodyHash = base64_encode(hash('sha256', $canonicalBody, true));

        // Canonicalize headers (relaxed)
        $canonicalHeaders = $this->canonicalizeHeaders($headers);

        // Create signature data
        $signatureData = "from:to:subject:date:message-id";
        $signatureData .= "\r\n" . $canonicalHeaders;

        // Sign with private key
        $signature = '';
        openssl_sign($signatureData, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);

        // Wrap signature (RFC 2047 style, max 76 chars per line)
        $signatureWrapped = chunk_split($signatureBase64, 76, "\r\n ");
        $signatureWrapped = rtrim($signatureWrapped);

        return [
            'body_hash' => $bodyHash,
            'signature' => $signatureWrapped,
        ];
    }

    /**
     * Canonicalize headers using relaxed algorithm.
     */
    protected function canonicalizeHeaders(string $headers): string
    {
        $lines = explode("\r\n", $headers);
        $canonical = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // Convert to lowercase and remove extra whitespace
            $line = strtolower(trim($line));
            
            // Split header name and value
            if (strpos($line, ':') !== false) {
                [$name, $value] = explode(':', $line, 2);
                $name = trim($name);
                $value = preg_replace('/\s+/', ' ', trim($value));
                $canonical[] = $name . ':' . $value;
            }
        }

        return implode("\r\n", $canonical);
    }

    /**
     * Canonicalize body using relaxed algorithm.
     */
    protected function canonicalizeRelaxed(string $body): string
    {
        // Remove trailing whitespace from lines
        $lines = explode("\r\n", $body);
        $canonical = [];

        foreach ($lines as $line) {
            $canonical[] = rtrim($line);
        }

        $canonicalBody = implode("\r\n", $canonical);

        // Reduce multiple consecutive empty lines to one
        $canonicalBody = preg_replace('/\r\n\r\n+/', "\r\n\r\n", $canonicalBody);

        // Ensure body ends with \r\n if not empty
        if (!empty($canonicalBody) && substr($canonicalBody, -2) !== "\r\n") {
            $canonicalBody .= "\r\n";
        }

        return $canonicalBody;
    }

    /**
     * Check if email should be signed and sign it if needed.
     */
    public function signEmailIfNeeded(string $emailContent, string $fromEmail, DeliveryServer $server = null, int $customerId = null): string
    {
        // Check if server already signs with DKIM
        if ($this->serverSignsWithDkim($server)) {
            Log::info('Skipping DKIM signing - server already signs', [
                'server_type' => $server?->type,
            ]);
            return $emailContent;
        }

        // Get sending domain for FROM email
        $sendingDomain = $this->getSendingDomainForEmail($fromEmail, $customerId);

        if (!$sendingDomain) {
            Log::debug('No verified sending domain found for email', [
                'from' => $fromEmail,
            ]);
            return $emailContent;
        }

        // Extract subject from email content
        $subject = $this->extractSubject($emailContent);

        // Sign the email
        return $this->signEmail($emailContent, $sendingDomain, $fromEmail, $subject);
    }

    /**
     * Extract subject from email headers.
     */
    protected function extractSubject(string $emailContent): string
    {
        if (preg_match('/^Subject:\s*(.+)$/mi', $emailContent, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }
}

