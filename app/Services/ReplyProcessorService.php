<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\CampaignReply;
use App\Models\CampaignRecipient;
use App\Models\ListSubscriber;
use App\Models\ReplyServer;
use Carbon\Carbon;
use App\Services\AutomationTriggerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReplyProcessorService
{
    public function processReplies(?ReplyServer $replyServer = null): int
    {
        // Allow processing if a specific reply server is provided, even if global tracking is disabled
        if (!$replyServer && !(bool) config('mailzen.reply_tracking.enabled', false)) {
            return 0;
        }

        if (!function_exists('imap_open')) {
            Log::error('IMAP extension is not available. Please install php-imap extension.');
            $this->logReplyServerError($replyServer, 'IMAP extension not available');
            throw new \Exception('IMAP extension is not available. Please install php-imap extension.');
        }

        $imap = $this->resolveImapSettings($replyServer);

        $hostname = trim((string) ($imap['hostname'] ?? ''));
        $username = trim((string) ($imap['username'] ?? ''));
        $password = (string) ($imap['password'] ?? '');
        $mailbox = trim((string) ($imap['mailbox'] ?? 'INBOX'));

        $protocol = strtolower((string) ($imap['protocol'] ?? 'imap'));
        if (!in_array($protocol, ['imap', 'pop3'], true)) {
            $protocol = 'imap';
        }

        $port = (int) ($imap['port'] ?? 993);
        $encryption = (string) ($imap['encryption'] ?? 'ssl');

        $deleteAfter = (bool) ($imap['delete_after_processing'] ?? false);
        $maxBatch = (int) ($imap['max_emails_per_batch'] ?? 50);
        if ($maxBatch <= 0) {
            $maxBatch = 50;
        }

        if ($hostname === '' || $username === '' || $password === '') {
            Log::warning('Reply tracking IMAP is enabled but missing connection settings.', [
                'reply_server_id' => $replyServer?->id,
            ]);
            $this->logReplyServerError($replyServer, 'Missing connection settings');
            return 0;
        }

        $connectionString = "{{$hostname}:{$port}";

        // Explicitly set protocol (defaults to IMAP if not provided, but POP3 needs the /pop3 flag)
        if ($protocol === 'pop3') {
            $connectionString .= '/pop3';
        } else {
            $connectionString .= '/imap';
        }
        
        // Determine encryption based on port and settings
        $useSsl = false;
        if ($encryption === 'ssl') {
            $useSsl = true;
        } elseif ($encryption === 'tls') {
            $useSsl = false; // TLS is different
        } elseif ($encryption === 'none' || empty($encryption)) {
            // Auto-detect based on port
            $useSsl = in_array($port, [993, 465, 995]);
        }
        
        if ($useSsl) {
            $connectionString .= '/ssl';
        } elseif ($encryption === 'tls') {
            $connectionString .= '/tls';
        }
        
        // Add novalidate-cert option if SSL validation is disabled
        $validateSsl = (bool) ($imap['validate_ssl'] ?? true);
        if (($useSsl || $encryption === 'tls')) {
            $connectionString .= '/novalidate-cert';
        }
        
        $connectionString .= '}';
        $connectionString .= $mailbox;

        // Add connection timeout and retry logic
        $startTime = now();
        $connection = $this->connectWithRetry($connectionString, $username, $password, $replyServer);
        
        if (!$connection) {
            $lastError = (string) (imap_last_error() ?: '');
            $errors = imap_errors();
            $errorsText = is_array($errors) && !empty($errors) ? implode(' | ', array_map('strval', $errors)) : '';

            $details = trim(($lastError !== '' ? $lastError : '') . ($errorsText !== '' ? ' | ' . $errorsText : ''));
            $details = $details !== '' ? $details : 'Unknown IMAP error';

            $this->logReplyServerError($replyServer, 'Failed to establish IMAP connection after retries: ' . $details);
            throw new \Exception('Failed to connect to reply inbox: ' . $details);
        }

        $connectionTime = now()->diffInMilliseconds($startTime);
        Log::info('IMAP connection established', [
            'reply_server_id' => $replyServer?->id,
            'hostname' => $hostname,
            'connection_time_ms' => $connectionTime,
        ]);

        try {
            $messages = imap_search($connection, 'UNSEEN');
            if (!$messages) {
                imap_close($connection);
                $this->logReplyServerProcess($replyServer, 0, 0, $connectionTime);
                return 0;
            }

            $totalMessages = count($messages);
            $messages = array_slice($messages, 0, $maxBatch);
            $batchSize = count($messages);

            Log::info('Found unread messages', [
                'reply_server_id' => $replyServer?->id,
                'total_unread' => $totalMessages,
                'batch_size' => $batchSize,
            ]);

            $processed = 0;
            $errors = 0;
            foreach ($messages as $messageNumber) {
                try {
                    $processed += $this->processIndividualMessage($connection, $messageNumber, $replyServer);
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error processing individual reply message', [
                        'reply_server_id' => $replyServer?->id,
                        'message_number' => $messageNumber,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Mark as read even if processing failed to avoid infinite loops
                    imap_setflag_full($connection, (string) $messageNumber, "\\Seen");
                    if ($deleteAfter) {
                        imap_delete($connection, $messageNumber);
                    }
                }
            }

            if ($deleteAfter) {
                imap_expunge($connection);
            }

            imap_close($connection);

            $totalTime = now()->diffInMilliseconds($startTime);
            $this->logReplyServerProcess($replyServer, $processed, $errors, $totalTime);

            Log::info('Reply processing completed', [
                'reply_server_id' => $replyServer?->id,
                'processed' => $processed,
                'errors' => $errors,
                'total_time_ms' => $totalTime,
            ]);

            return $processed;

        } catch (\Exception $e) {
            imap_close($connection);
            $this->logReplyServerError($replyServer, 'Processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function resolveImapSettings(?ReplyServer $replyServer = null): array
    {
        if ($replyServer) {
            return [
                'protocol' => (string) ($replyServer->protocol ?? 'imap'),
                'hostname' => (string) ($replyServer->hostname ?? ''),
                'username' => (string) ($replyServer->username ?? ''),
                'password' => (string) ($replyServer->password ?? ''),
                'mailbox' => (string) ($replyServer->mailbox ?? 'INBOX'),
                'port' => (int) ($replyServer->port ?? 993),
                'encryption' => (string) ($replyServer->encryption ?? 'ssl'),
                'delete_after_processing' => (bool) ($replyServer->delete_after_processing ?? false),
                'max_emails_per_batch' => (int) ($replyServer->max_emails_per_batch ?? 50),
                'validate_ssl' => (bool) ($replyServer->validate_ssl ?? true),
            ];
        }

        return (array) config('mailzen.reply_tracking.imap', []);
    }

    protected function forwardReply(Campaign $campaign, CampaignRecipient $recipient, string $raw, ?string $from, ?string $subject): void
    {
        $destination = (string) ($campaign->reply_to ?? '');
        if ($destination === '') {
            $campaign->loadMissing('deliveryServer', 'emailList');

            $destination = (string) ($campaign->deliveryServer?->reply_to_email ?? '');
            if ($destination === '') {
                $destination = (string) ($campaign->emailList?->reply_to ?? '');
            }
            if ($destination === '') {
                $destination = (string) ($campaign->from_email ?? config('mail.from.address'));
            }
        }

        if ($destination === '') {
            return;
        }

        $body = $this->extractPlainBody($raw);

        $forwardSubject = 'FWD: ' . (string) ($subject ?? 'Campaign reply');

        try {
            Mail::raw($body, function ($message) use ($destination, $forwardSubject, $from, $campaign, $recipient) {
                $message->to($destination)
                    ->subject($forwardSubject);

                if (is_string($from) && trim($from) !== '') {
                    $message->replyTo($from);
                }

                $message->getHeaders()->addTextHeader('X-MailZen-Reply-Forwarded', '1');
                $message->getHeaders()->addTextHeader('X-Campaign-ID', (string) $campaign->id);
                $message->getHeaders()->addTextHeader('X-Recipient-UUID', (string) $recipient->uuid);
            });
        } catch (\Throwable $e) {
            Log::warning('Failed to forward reply', [
                'campaign_id' => $campaign->id,
                'recipient_uuid' => $recipient->uuid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function isForwardedMessage(string $raw): bool
    {
        return stripos($raw, 'X-MailZen-Reply-Forwarded:') !== false;
    }

    protected function extractRecipientUuid(string $raw): ?string
    {
        $candidates = [
            $this->extractHeaderValue($raw, 'To'),
            $this->extractHeaderValue($raw, 'Delivered-To'),
            $this->extractHeaderValue($raw, 'X-Original-To'),
        ];

        foreach ($candidates as $value) {
            if (!is_string($value) || trim($value) === '') {
                continue;
            }

            if (preg_match('/reply\+([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})@/i', $value, $m)) {
                return strtolower($m[1]);
            }
        }

        // Try to extract UUID from custom headers in the email body (quoted replies)
        $bodyText = $this->extractPlainBody($raw);
        $uuidFromHeaders = $this->extractUuidFromQuotedHeaders($bodyText);
        if ($uuidFromHeaders) {
            return $uuidFromHeaders;
        }

        // Try to find recipient using In-Reply-To and References headers
        $uuidFromReferences = $this->extractUuidFromReferences($raw);
        if ($uuidFromReferences) {
            return $uuidFromReferences;
        }

        // Try to find recipient by matching email content and sender info
        $from = $this->extractHeaderValue($raw, 'From');
        $subject = $this->extractHeaderValue($raw, 'Subject');
        
        if ($from && $subject) {
            return $this->findRecipientByContent($from, $subject, $bodyText);
        }

        return null;
    }

    protected function extractUuidFromReferences(string $raw): ?string
    {
        // Look for UUID in In-Reply-To and References headers
        $inReplyTo = $this->extractHeaderValue($raw, 'In-Reply-To');
        $references = $this->extractHeaderValue($raw, 'References');
        
        $headersToCheck = array_filter([$inReplyTo, $references]);
        
        foreach ($headersToCheck as $headerValue) {
            // Look for UUID patterns in message IDs
            if (preg_match('/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i', $headerValue, $matches)) {
                $uuid = $matches[1];
                
                // Check if this UUID exists as a campaign recipient
                $recipient = CampaignRecipient::where('uuid', strtolower($uuid))->first();
                if ($recipient) {
                    return strtolower($uuid);
                }
            }
        }

        return null;
    }

    protected function extractUuidFromQuotedHeaders(string $bodyText): ?string
    {
        // Look for custom headers in quoted email content
        $patterns = [
            '/X-Recipient-UUID:\s*([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/X-Campaign-ID:\s*(\d+).*?X-Recipient-UUID:\s*([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $bodyText, $matches)) {
                // Return the UUID (might be in different positions depending on pattern)
                $uuid = end($matches);
                if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid)) {
                    return strtolower($uuid);
                }
            }
        }

        return null;
    }

    protected function findRecipientByContent(string $from, string $subject, string $bodyText): ?string
    {
        // Extract email address from From header
        $fromEmail = $this->parseFromHeader($from)['email'] ?? null;
        if (!$fromEmail) {
            return null;
        }

        // Look for campaign recipients with this email who haven't replied yet
        $recipients = CampaignRecipient::query()
            ->where('email', strtolower($fromEmail))
            ->where('status', '!=', 'replied')
            ->with('campaign')
            ->get();

        if ($recipients->isEmpty()) {
            return null;
        }

        // Try to match by subject (looking for "Re:" patterns)
        foreach ($recipients as $recipient) {
            if ($recipient->campaign && $this->isReplyToCampaign($subject, $recipient->campaign)) {
                return $recipient->uuid;
            }
        }

        // If no subject match, try to match by body content
        foreach ($recipients as $recipient) {
            if ($recipient->campaign && $this->isReplyToCampaignByBody($bodyText, $recipient->campaign)) {
                return $recipient->uuid;
            }
        }

        // If still no match, return the most recent recipient
        return $recipients->sortByDesc('created_at')->first()->uuid;
    }

    protected function isReplyToCampaign(string $replySubject, Campaign $campaign): bool
    {
        // Check if reply subject matches campaign subject
        $campaignSubject = strtolower($campaign->subject ?? '');
        $replySubject = strtolower($replySubject);
        
        // Look for "re:" prefix and campaign subject
        if (str_starts_with($replySubject, 're:')) {
            $replySubject = substr($replySubject, 3);
            $replySubject = trim($replySubject);
        }
        
        return str_contains($replySubject, $campaignSubject) || 
               str_contains($campaignSubject, $replySubject);
    }

    protected function isReplyToCampaignByBody(string $bodyText, Campaign $campaign): bool
    {
        // Look for campaign-specific content in the body
        $campaignName = strtolower($campaign->name ?? '');
        $campaignSubject = strtolower($campaign->subject ?? '');
        $bodyText = strtolower($bodyText);
        
        return str_contains($bodyText, $campaignName) || 
               str_contains($bodyText, $campaignSubject);
    }

    protected function extractHeaderValue(string $raw, string $headerName): ?string
    {
        $pattern = '/^' . preg_quote($headerName, '/') . ':(.*)$/im';
        if (!preg_match($pattern, $raw, $matches)) {
            return null;
        }

        $value = trim((string) ($matches[1] ?? ''));

        return $value !== '' ? $value : null;
    }

    protected function extractPlainBody(string $raw): string
    {
        return self::decodeBodyText($raw);
    }

    public static function decodeBodyText(string $raw): string
    {
        $raw = (string) $raw;
        if (trim($raw) === '') {
            return '';
        }

        [$headersCandidate, $bodyCandidate] = self::splitHeadersAndBody($raw);
        $looksLikeHeaders = self::looksLikeRfc822Headers($headersCandidate);

        $headers = $looksLikeHeaders ? $headersCandidate : '';
        $body = $looksLikeHeaders ? $bodyCandidate : $raw;

        $decoded = self::extractBestTextPart($headers, $body, 0);

        return trim($decoded);
    }

    protected static function looksLikeRfc822Headers(string $headers): bool
    {
        $headers = trim($headers);
        if ($headers === '') {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z0-9-]+\s*:\s*.+$/m', $headers);
    }

    protected static function splitHeadersAndBody(string $raw): array
    {
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $parts = preg_split("/\n\n/", $raw, 2);
        if (!$parts) {
            return ['', $raw];
        }

        if (count($parts) < 2) {
            return ['', $raw];
        }

        return [(string) ($parts[0] ?? ''), (string) ($parts[1] ?? '')];
    }

    protected static function extractBestTextPart(string $headers, string $body, int $depth): string
    {
        if ($depth > 10) {
            return '';
        }

        $contentType = self::extractHeaderValueFromString($headers, 'Content-Type');
        $transferEncoding = self::extractHeaderValueFromString($headers, 'Content-Transfer-Encoding');

        $boundary = self::extractBoundaryFromContentType($contentType);
        if ($boundary === null) {
            $boundary = self::inferBoundaryFromBody($body);
        }

        $isMultipart = $contentType !== null && stripos($contentType, 'multipart/') !== false;
        if ($boundary !== null && ($isMultipart || self::looksLikeMultipartBody($body, $boundary))) {
            [$plain, $html] = self::extractTextFromMultipartBody($body, $boundary, $depth);
            if ($plain !== '') {
                return $plain;
            }
            if ($html !== '') {
                return self::htmlToText($html);
            }

            return '';
        }

        $partType = self::normalizeMimeType($contentType);
        $decodedBody = self::decodeTransferEncoding($body, $transferEncoding);
        $decodedBody = self::decodeCharsetToUtf8($decodedBody, self::extractCharsetFromContentType($contentType));

        if ($partType === 'text/html') {
            return self::htmlToText($decodedBody);
        }

        if ($partType !== null && $partType !== 'text/plain') {
            return '';
        }

        return (string) $decodedBody;
    }

    protected static function extractTextFromMultipartBody(string $body, string $boundary, int $depth): array
    {
        $normalizedBody = str_replace(["\r\n", "\r"], "\n", $body);
        $delimiter = "--" . $boundary;

        $segments = explode($delimiter, $normalizedBody);
        if (count($segments) <= 1) {
            return ['', ''];
        }

        $plain = '';
        $html = '';

        foreach ($segments as $idx => $segment) {
            if ($idx === 0) {
                continue;
            }

            $segment = ltrim((string) $segment, "\n");
            if ($segment === '' || str_starts_with($segment, '--')) {
                continue;
            }

            [$partHeaders, $partBody] = self::splitHeadersAndBody($segment);
            $partContentType = self::extractHeaderValueFromString($partHeaders, 'Content-Type');
            $partType = self::normalizeMimeType($partContentType);

            if ($partType === null && $partHeaders === '') {
                $partType = 'text/plain';
            }

            $text = self::extractBestTextPart($partHeaders, $partBody, $depth + 1);
            if ($text === '') {
                continue;
            }

            if ($plain === '' && ($partType === null || $partType === 'text/plain')) {
                $plain = $text;
            } elseif ($html === '' && $partType === 'text/html') {
                $html = $text;
            } elseif ($plain === '' && $partType === null) {
                $plain = $text;
            }
        }

        return [$plain, $html];
    }

    protected static function looksLikeMultipartBody(string $body, string $boundary): bool
    {
        $normalizedBody = str_replace(["\r\n", "\r"], "\n", $body);
        return str_contains($normalizedBody, "--" . $boundary . "\n") || str_starts_with(trim($normalizedBody), "--" . $boundary);
    }

    protected static function inferBoundaryFromBody(string $body): ?string
    {
        $normalizedBody = str_replace(["\r\n", "\r"], "\n", $body);
        if (preg_match('/^\s*--([^\n\r]+)\s*$/m', $normalizedBody, $m)) {
            $candidate = trim((string) ($m[1] ?? ''));
            $candidate = rtrim($candidate, "-");
            return $candidate !== '' ? $candidate : null;
        }

        return null;
    }

    protected static function extractHeaderValueFromString(string $headers, string $headerName): ?string
    {
        if (trim($headers) === '') {
            return null;
        }

        $pattern = '/^' . preg_quote($headerName, '/') . ':(.*)$/im';
        if (!preg_match($pattern, $headers, $matches)) {
            return null;
        }

        $value = trim((string) ($matches[1] ?? ''));
        return $value !== '' ? $value : null;
    }

    protected static function extractBoundaryFromContentType(?string $contentType): ?string
    {
        if (!is_string($contentType) || trim($contentType) === '') {
            return null;
        }

        if (!preg_match('/boundary\s*=\s*("([^"]+)"|([^;\s]+))/i', $contentType, $m)) {
            return null;
        }

        $boundary = (string) ($m[2] ?? ($m[3] ?? ''));
        $boundary = trim($boundary);

        return $boundary !== '' ? $boundary : null;
    }

    protected static function extractCharsetFromContentType(?string $contentType): ?string
    {
        if (!is_string($contentType) || trim($contentType) === '') {
            return null;
        }

        if (!preg_match('/charset\s*=\s*("([^"]+)"|([^;\s]+))/i', $contentType, $m)) {
            return null;
        }

        $charset = (string) ($m[2] ?? ($m[3] ?? ''));
        $charset = trim($charset);

        return $charset !== '' ? $charset : null;
    }

    protected static function normalizeMimeType(?string $contentType): ?string
    {
        if (!is_string($contentType) || trim($contentType) === '') {
            return null;
        }

        $type = strtolower(trim((string) explode(';', $contentType, 2)[0]));
        return $type !== '' ? $type : null;
    }

    protected static function decodeTransferEncoding(string $body, ?string $transferEncoding): string
    {
        $encoding = strtolower(trim((string) ($transferEncoding ?? '')));
        $body = (string) $body;

        if ($encoding === 'base64') {
            $compact = preg_replace('/\s+/', '', $body);
            $decoded = base64_decode((string) $compact, true);
            return is_string($decoded) ? $decoded : $body;
        }

        if ($encoding === 'quoted-printable') {
            return quoted_printable_decode($body);
        }

        return $body;
    }

    protected static function decodeCharsetToUtf8(string $body, ?string $charset): string
    {
        $charset = trim((string) ($charset ?? ''));
        if ($charset === '') {
            return $body;
        }

        $charsetLower = strtolower($charset);
        if ($charsetLower === 'utf-8' || $charsetLower === 'utf8') {
            return $body;
        }

        if (function_exists('iconv')) {
            $converted = @iconv($charset, 'UTF-8//TRANSLIT', $body);
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        return $body;
    }

    protected static function htmlToText(string $html): string
    {
        $html = preg_replace('/<\s*br\s*\/?\s*>/i', "\n", $html);
        $html = preg_replace('/<\s*\/?p\b[^>]*>/i', "\n", $html);
        $html = preg_replace('/<\s*\/?div\b[^>]*>/i', "\n", $html);

        $text = strip_tags((string) $html);

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return (string) $text;
    }

    protected function normalizeMessageId(?string $messageId): string
    {
        if (!is_string($messageId)) {
            return '';
        }

        $messageId = trim($messageId);
        $messageId = trim($messageId, "<> \t\n\r\0\x0B");

        return $messageId;
    }

    protected function parseFromHeader(?string $from): array
    {
        $from = trim((string) ($from ?? ''));
        if ($from === '') {
            return ['email' => null, 'name' => null];
        }

        if (preg_match('/^(.*)<\s*([^>]+)\s*>$/', $from, $m)) {
            $name = trim((string) ($m[1] ?? ''));
            $email = trim((string) ($m[2] ?? ''));
            $name = trim($name, "\"' \t\n\r\0\x0B");

            return [
                'email' => $email !== '' ? $email : null,
                'name' => $name !== '' ? $name : null,
            ];
        }

        return [
            'email' => $from,
            'name' => null,
        ];
    }

    protected function parseEmailDate(?string $date): ?Carbon
    {
        $date = trim((string) ($date ?? ''));
        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function connectWithRetry(string $connectionString, string $username, string $password, ?ReplyServer $replyServer = null): mixed
    {
        $maxRetries = 3;
        $retryDelay = 1000; // 1 second in milliseconds
        $lastError = '';

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            $connection = @imap_open($connectionString, $username, $password, NULL, 1);
            
            if ($connection) {
                return $connection;
            }

            $error = (string) (imap_last_error() ?: '');
            if ($error !== '') {
                $lastError = $error;
            }
            
            if ($attempt < $maxRetries) {
                Log::warning('IMAP connection failed, retrying', [
                    'reply_server_id' => $replyServer?->id,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $error,
                ]);
                
                usleep($retryDelay * 1000); // Convert to microseconds
                $retryDelay *= 2; // Exponential backoff
            }
        }

        Log::error('IMAP connection failed after all retries', [
            'reply_server_id' => $replyServer?->id,
            'max_retries' => $maxRetries,
            'final_error' => $lastError !== '' ? $lastError : $error,
        ]);

        return false;
    }

    protected function processIndividualMessage($connection, int $messageNumber, ?ReplyServer $replyServer = null): int
    {
        $header = (string) imap_fetchheader($connection, $messageNumber);
        $body = (string) imap_body($connection, $messageNumber);
        $raw = $header . "\r\n\r\n" . $body;

        if ($this->isForwardedMessage($raw)) {
            imap_setflag_full($connection, (string) $messageNumber, "\\Seen");
            return 0;
        }

        $recipientUuid = $this->extractRecipientUuid($raw);
        if (!$recipientUuid) {
            imap_setflag_full($connection, (string) $messageNumber, "\\Seen");
            return 0;
        }

        $from = $this->extractHeaderValue($raw, 'From');
        $subject = $this->extractHeaderValue($raw, 'Subject');
        $messageId = $this->extractHeaderValue($raw, 'Message-ID');
        $date = $this->extractHeaderValue($raw, 'Date');
        $bodyText = $this->extractPlainBody($raw);

        $recipient = CampaignRecipient::query()->where('uuid', $recipientUuid)->first();
        if (!$recipient) {
            imap_setflag_full($connection, (string) $messageNumber, "\\Seen");
            return 0;
        }

        $recipient->loadMissing('campaign');
        $campaign = $recipient->campaign;

        $messageKey = $this->normalizeMessageId($messageId);
        $wasReplied = $recipient->isReplied();

        DB::transaction(function () use ($campaign, $recipient, $from, $subject, $messageId, $messageKey, $date, $bodyText) {
            $already = $recipient->isReplied();
            if (!$already) {
                $recipient->markAsReplied();
                if ($campaign) {
                    $campaign->increment('replied_count');
                }
            }

            if ($campaign) {
                CampaignLog::logEvent(
                    $campaign->id,
                    'replied',
                    $recipient->id,
                    [
                        'email' => $recipient->email,
                        'from' => $from,
                        'subject' => $subject,
                        'message_id' => $messageId,
                    ]
                );

                $parsedFrom = $this->parseFromHeader($from);
                $receivedAt = $this->parseEmailDate($date);

                $payload = [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                    'message_id' => $messageKey !== '' ? $messageKey : null,
                    'from_email' => $parsedFrom['email'] ?? null,
                    'from_name' => $parsedFrom['name'] ?? null,
                    'subject' => $subject,
                    'body_text' => $bodyText,
                    'received_at' => $receivedAt,
                ];

                if ($messageKey !== '') {
                    CampaignReply::updateOrCreate(
                        ['campaign_id' => $campaign->id, 'message_id' => $messageKey],
                        $payload
                    );
                } else {
                    CampaignReply::create($payload);
                }
            }
        });

        if (!$wasReplied && $campaign) {
            $subscriber = ListSubscriber::query()
                ->where('list_id', $campaign->list_id)
                ->where('email', strtolower(trim((string) ($recipient->email ?? ''))))
                ->first();

            if ($subscriber) {
                try {
                    app(AutomationTriggerService::class)->triggerSubscriberEvent('campaign_replied', $subscriber, [
                        'campaign_id' => $campaign->id,
                        'recipient_id' => $recipient->id,
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('Failed to trigger automation on campaign_replied', [
                        'campaign_id' => $campaign->id,
                        'recipient_id' => $recipient->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        if ($campaign) {
            $this->forwardReply($campaign, $recipient, $raw, $from, $subject);
        }

        imap_setflag_full($connection, (string) $messageNumber, "\\Seen");
        
        return 1;
    }

    protected function logReplyServerProcess(?ReplyServer $replyServer, int $processed, int $errors, int $totalTime): void
    {
        if (!$replyServer) return;

        $logData = [
            'processed' => $processed,
            'errors' => $errors,
            'total_time_ms' => $totalTime,
            'processed_at' => now()->toIso8601String(),
        ];

        // Get existing logs or create new array
        $logs = $replyServer->process_logs ?? [];
        $logs[] = $logData;

        // Keep only last 100 log entries
        $logs = array_slice($logs, -100);

        $replyServer->process_logs = $logs;
        $replyServer->last_processed_at = now();
        $replyServer->save();
    }

    protected function logReplyServerError(?ReplyServer $replyServer, string $error): void
    {
        if (!$replyServer) return;

        $logData = [
            'error' => $error,
            'error_at' => now()->toIso8601String(),
        ];

        // Get existing error logs or create new array
        $logs = $replyServer->error_logs ?? [];
        $logs[] = $logData;

        // Keep only last 50 error entries
        $logs = array_slice($logs, -50);

        $replyServer->error_logs = $logs;
        $replyServer->last_error_at = now();
        $replyServer->save();
    }
}
