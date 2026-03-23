<?php

namespace App\Mail;

use App\Models\DeliveryServer;
use App\Models\SendingDomain;
use App\Services\DkimSigningService;
use Illuminate\Support\Facades\Log;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;
use Swift_Message;

class DkimSigningPlugin implements Swift_Events_SendListener
{
    public function __construct(
        protected DkimSigningService $dkimSigningService
    ) {}

    /**
     * Invoked immediately before the Message is sent.
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt): void
    {
        $message = $evt->getMessage();
        
        if (!$message instanceof Swift_Message) {
            return;
        }

        // Get FROM email address
        $fromAddresses = $message->getFrom();
        if (empty($fromAddresses)) {
            return;
        }

        $fromEmail = array_key_first($fromAddresses);
        if (!$fromEmail) {
            return;
        }

        // Extract domain from FROM email
        $domain = substr(strrchr($fromEmail, '@'), 1);
        if (!$domain) {
            return;
        }

        // Get customer ID from message headers (if available)
        $customerId = null;
        $headers = $message->getHeaders();
        if ($headers->has('X-Customer-ID')) {
            $customerId = (int) $headers->get('X-Customer-ID')->getValue();
        }

        // Get delivery server (if available)
        $server = null;
        if ($headers->has('X-Delivery-Server-ID')) {
            $serverId = (int) $headers->get('X-Delivery-Server-ID')->getValue();
            $server = DeliveryServer::find($serverId);
        }

        // Check if server already signs with DKIM
        if ($this->dkimSigningService->serverSignsWithDkim($server)) {
            return; // Server already signs, skip
        }

        // Get sending domain for this email
        $sendingDomain = $this->dkimSigningService->getSendingDomainForEmail($fromEmail, $customerId);

        if (!$sendingDomain || !$sendingDomain->isVerified()) {
            return; // No verified sending domain found
        }

        // Sign the message with DKIM
        $this->signMessage($message, $sendingDomain, $domain);
    }

    /**
     * Sign Swift message with DKIM.
     */
    protected function signMessage(Swift_Message $message, SendingDomain $sendingDomain, string $domain): void
    {
        if (!$sendingDomain->dkim_private_key) {
            Log::warning('Sending domain has no DKIM private key', [
                'domain' => $sendingDomain->domain,
            ]);
            return;
        }

        try {
            $selector = 'mail';
            $privateKey = $sendingDomain->dkim_private_key;

            // Get message headers and body
            $headers = $message->getHeaders();
            $body = $message->getBody();

            // Create canonicalized headers for signing
            $headersToSign = ['From', 'To', 'Subject', 'Date', 'Message-ID'];
            $canonicalHeaders = [];
            
            foreach ($headersToSign as $headerName) {
                if ($headers->has($headerName)) {
                    $header = $headers->get($headerName);
                    $value = $header->getFieldBody();
                    $canonicalHeaders[] = strtolower($headerName) . ':' . preg_replace('/\s+/', ' ', trim($value));
                }
            }

            $canonicalHeadersString = implode("\r\n", $canonicalHeaders);

            // Canonicalize body
            $canonicalBody = $this->canonicalizeBody($body);

            // Hash body
            $bodyHash = base64_encode(hash('sha256', $canonicalBody, true));

            // Create signature data
            $signatureData = implode(':', array_map('strtolower', $headersToSign));
            $signatureData .= "\r\n" . $canonicalHeadersString;

            // Sign with private key
            $signature = '';
            $privateKeyResource = openssl_pkey_get_private($privateKey);
            
            if (!$privateKeyResource) {
                Log::error('Failed to load DKIM private key', [
                    'domain' => $sendingDomain->domain,
                ]);
                return;
            }

            openssl_sign($signatureData, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256);
            openssl_free_key($privateKeyResource);
            
            $signatureBase64 = base64_encode($signature);
            $signatureWrapped = chunk_split($signatureBase64, 76, "\r\n ");
            $signatureWrapped = rtrim($signatureWrapped);

            // Create DKIM-Signature header
            $dkimHeader = "v=1; a=rsa-sha256; c=relaxed/relaxed; d={$domain}; s={$selector}; " .
                         "h=" . implode(':', array_map('strtolower', $headersToSign)) . "; " .
                         "bh={$bodyHash}; " .
                         "b={$signatureWrapped}";

            // Add DKIM-Signature header to message
            $headers->addTextHeader('DKIM-Signature', $dkimHeader);

            Log::info('DKIM signature added to email', [
                'domain' => $domain,
                'from' => $fromEmail ?? 'unknown',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to sign email with DKIM', [
                'domain' => $sendingDomain->domain,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Canonicalize body using relaxed algorithm.
     */
    protected function canonicalizeBody(string $body): string
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
     * Invoked immediately after the Message is sent.
     */
    public function sendPerformed(Swift_Events_SendEvent $evt): void
    {
        // Nothing to do after sending
    }
}

