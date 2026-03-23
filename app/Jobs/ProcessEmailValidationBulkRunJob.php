<?php

namespace App\Jobs;

use App\Models\EmailValidationRun;
use App\Models\EmailValidationRunItem;
use App\Models\ListSubscriber;
use App\Models\SuppressionList;
use App\Models\UsageLog;
use App\Services\EmailValidation\SnapvalidClient;
use App\Services\ListSubscriberService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessEmailValidationBulkRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 20;
    public int $backoff = 60;
    public int $timeout = 300;

    public function __construct(
        public EmailValidationRun $run
    ) {
    }

    public function handle(
        SnapvalidClient $snapvalidClient,
        ListSubscriberService $subscriberService
    ): void {
        $this->run->refresh();

        $settings = is_array($this->run->settings) ? $this->run->settings : [];
        if ((bool) data_get($settings, 'is_paused', false)) {
            $this->release(60);
            return;
        }

        if ($this->run->status !== 'running') {
            return;
        }

        $this->run->loadMissing(['customer', 'tool']);
        $customer = $this->run->customer;
        $tool = $this->run->tool;

        if (!$customer || !$tool || !$tool->active) {
            $this->failRun('Email validation tool is missing or inactive.');
            return;
        }

        if ($tool->provider !== 'snapvalid') {
            $this->failRun('Bulk validation is supported only for Snapvalid.');
            return;
        }

        $fileUploadsId = isset($settings['snapvalid_file_uploads_id']) && is_numeric($settings['snapvalid_file_uploads_id'])
            ? (int) $settings['snapvalid_file_uploads_id']
            : null;

        if (!$fileUploadsId) {
            $settings = is_array($this->run->settings) ? $this->run->settings : [];
            if (!empty($settings['snapvalid_bulk_fallback'])) {
                return;
            }

            $emails = DB::table('list_subscribers')
                ->where('list_id', $this->run->list_id)
                ->whereNull('deleted_at')
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->select('email')
                ->distinct()
                ->orderBy('email')
                ->pluck('email')
                ->map(fn ($e) => strtolower(trim((string) $e)))
                ->filter()
                ->values();

            if ($emails->isEmpty()) {
                $this->run->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
                return;
            }

            $content = $emails->implode("\n") . "\n";

            $upload = $snapvalidClient->uploadBulkEmails((string) $tool->api_key, $content, 'emails.txt');
            if (!($upload['success'] ?? false) || !is_int($upload['file_uploads_id'] ?? null)) {
                if ($this->isInsufficientCreditsPayload($upload)) {
                    $this->pauseRunForInsufficientCredits();
                    return;
                }

                if ($this->isRateLimitedPayload($upload)) {
                    Log::warning('Snapvalid bulk upload rate limited (HTTP 429). Retrying.', [
                        'run_id' => $this->run->id,
                        'tool_id' => $tool->id,
                    ]);
                    $this->release((int) config('mailzen.email_validation.snapvalid_429_delay_seconds', 10));
                    return;
                }

                Log::error('Snapvalid bulk upload failed', [
                    'run_id' => $this->run->id,
                    'tool_id' => $tool->id,
                    'upload' => $upload,
                ]);

                $reason = is_string($upload['message'] ?? null) && trim((string) $upload['message']) !== ''
                    ? (string) $upload['message']
                    : 'Snapvalid bulk upload failed.';

                $raw = is_array($upload['raw'] ?? null) ? $upload['raw'] : null;
                if ($raw) {
                    $status = $raw['status'] ?? null;
                    $body = $raw['body'] ?? null;
                    if (is_numeric($status) || is_string($body)) {
                        $reason .= ' ';
                        if (is_numeric($status)) {
                            $reason .= "(HTTP {$status}) ";
                        }
                        if (is_string($body) && $body !== '') {
                            $reason .= substr($body, 0, 500);
                        }
                    }
                }

                $this->run->refresh();
                $settings = is_array($this->run->settings) ? $this->run->settings : [];
                $settings['snapvalid_bulk_fallback'] = true;
                $settings['snapvalid_bulk_fallback_reason'] = $reason;
                $this->run->update([
                    'settings' => $settings,
                ]);

                $baseQuery = DB::table('list_subscribers')
                    ->where('list_id', $this->run->list_id)
                    ->whereNull('deleted_at')
                    ->whereNotNull('email')
                    ->where('email', '!=', '');

                $distinctIdSubquery = (clone $baseQuery)
                    ->selectRaw('MIN(id) as id')
                    ->groupBy('email');

                $chunkSize = 100;
                DB::query()
                    ->fromSub($distinctIdSubquery, 't')
                    ->select(['id'])
                    ->orderBy('id')
                    ->chunkById($chunkSize, function ($rows) {
                        $ids = collect($rows)->pluck('id')->filter()->values()->all();
                        if (empty($ids)) {
                            return;
                        }

                        ProcessEmailValidationChunkJob::dispatch($this->run, $ids)
                            ->onQueue('email-validation');
                    }, 'id');

                return;
            }

            $settings['snapvalid_file_uploads_id'] = (int) $upload['file_uploads_id'];
            $settings['snapvalid_bulk_uploaded_at'] = now()->toDateTimeString();

            $this->run->update([
                'settings' => $settings,
            ]);

            $this->release(60);
            return;
        }

        $progress = $snapvalidClient->checkQueueProgress((string) $tool->api_key);
        if (!($progress['success'] ?? false)) {
            if ($this->isInsufficientCreditsPayload($progress)) {
                $this->pauseRunForInsufficientCredits();
                return;
            }

            if ($this->isRateLimitedPayload($progress)) {
                Log::warning('Snapvalid queue progress rate limited (HTTP 429). Retrying.', [
                    'run_id' => $this->run->id,
                    'tool_id' => $tool->id,
                ]);
                $this->release((int) config('mailzen.email_validation.snapvalid_429_delay_seconds', 10));
                return;
            }

            $this->failRun((string) ($progress['message'] ?? 'Failed to check Snapvalid queue progress.'));
            return;
        }

        $remaining = is_int($progress['remaining'] ?? null) ? (int) $progress['remaining'] : null;
        if ($remaining === null) {
            $this->failRun('Snapvalid queue progress returned invalid remaining value.');
            return;
        }

        if ($remaining > 0) {
            $this->release(60);
            return;
        }

        $download = $snapvalidClient->downloadBulkResult((string) $tool->api_key, $fileUploadsId, '.csv');
        if (!($download['success'] ?? false) || !is_string($download['body'] ?? null)) {
            if ($this->isInsufficientCreditsPayload($download)) {
                $this->pauseRunForInsufficientCredits();
                return;
            }

            if ($this->isRateLimitedPayload($download)) {
                Log::warning('Snapvalid bulk result download rate limited (HTTP 429). Retrying.', [
                    'run_id' => $this->run->id,
                    'tool_id' => $tool->id,
                    'file_uploads_id' => $fileUploadsId,
                ]);
                $this->release((int) config('mailzen.email_validation.snapvalid_429_delay_seconds', 10));
                return;
            }

            $this->release(60);
            return;
        }

        $csv = (string) $download['body'];

        $rows = preg_split("/\r\n|\n|\r/", $csv);
        if (!is_array($rows) || count($rows) === 0) {
            $this->failRun('Snapvalid bulk result download returned empty content.');
            return;
        }

        $header = null;
        $parsedRows = [];
        foreach ($rows as $row) {
            $row = trim((string) $row);
            if ($row === '') {
                continue;
            }

            $cols = str_getcsv($row);
            if (!is_array($cols) || count($cols) === 0) {
                continue;
            }

            if ($header === null) {
                $header = array_map(fn ($h) => strtolower(trim((string) $h)), $cols);
                continue;
            }

            $data = [];
            foreach ($header as $idx => $key) {
                $data[$key] = array_key_exists($idx, $cols) ? $cols[$idx] : null;
            }

            $parsedRows[] = $data;
        }

        if (empty($parsedRows)) {
            $this->failRun('Snapvalid bulk result contains no rows.');
            return;
        }

        $emailKeys = collect($parsedRows)
            ->map(function ($r) {
                $email = $r['email'] ?? $r['Email'] ?? $r['e-mail'] ?? null;
                return strtolower(trim((string) $email));
            })
            ->filter()
            ->values()
            ->unique()
            ->all();

        $subscriberIdByEmail = [];
        foreach (array_chunk($emailKeys, 500) as $chunk) {
            $subs = ListSubscriber::query()
                ->where('list_id', $this->run->list_id)
                ->whereNull('deleted_at')
                ->whereIn('email', $chunk)
                ->orderBy('id')
                ->get(['id', 'email']);

            foreach ($subs as $sub) {
                $k = strtolower(trim((string) $sub->email));
                if ($k === '' || array_key_exists($k, $subscriberIdByEmail)) {
                    continue;
                }

                $subscriberIdByEmail[$k] = (int) $sub->id;
            }
        }

        $now = Carbon::now();
        $periodStart = $now->copy()->startOfMonth()->toDateString();
        $periodEnd = $now->copy()->endOfMonth()->toDateString();

        DB::transaction(function () use (
            $customer,
            $periodStart,
            $periodEnd,
            $settings,
            $parsedRows,
            $subscriberIdByEmail,
            $subscriberService
        ) {
            $settingsLocal = $settings;

            if (empty($settingsLocal['bulk_usage_logged'])) {
                $keys = [
                    'customer_id' => $customer->id,
                    'metric' => 'email_validation_emails_this_month',
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                ];

                DB::table('usage_logs')->insertOrIgnore(array_merge($keys, [
                    'amount' => 0,
                    'context' => json_encode([]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $log = UsageLog::where($keys)->first();
                if (!$log) {
                    throw new \RuntimeException('Usage log could not be created.');
                }

                $log->increment('amount', (int) $this->run->total_emails);
                $log->update([
                    'context' => array_merge($log->context ?? [], ['run_id' => $this->run->id]),
                ]);

                $settingsLocal['bulk_usage_logged'] = true;
                $this->run->update([
                    'settings' => $settingsLocal,
                ]);
            }

            foreach ($parsedRows as $r) {
                $email = strtolower(trim((string) ($r['email'] ?? $r['Email'] ?? $r['e-mail'] ?? '')));
                if ($email === '') {
                    continue;
                }

                $resultRaw = $r['result'] ?? $r['Result'] ?? null;
                $result = is_string($resultRaw) ? strtolower(trim($resultRaw)) : null;

                $successRaw = $r['success'] ?? $r['Success'] ?? null;
                $success = null;
                if (is_bool($successRaw)) {
                    $success = $successRaw;
                } elseif (is_numeric($successRaw)) {
                    $success = ((int) $successRaw) === 1;
                } elseif (is_string($successRaw)) {
                    $v = strtolower(trim($successRaw));
                    $success = in_array($v, ['1', 'true', 'yes'], true);
                }

                if ($success === null) {
                    $success = $result !== null;
                }

                $normalizedResult = match ($result) {
                    'deliverable' => 'deliverable',
                    'undeliverable' => 'undeliverable',
                    'accept_all', 'accept-all' => 'accept_all',
                    'unknown' => 'unknown',
                    default => ($success ? 'unknown' : null),
                };

                $message = $r['message'] ?? $r['Message'] ?? null;
                $message = is_string($message) ? $message : null;

                $subscriberId = $subscriberIdByEmail[$email] ?? null;

                $item = EmailValidationRunItem::updateOrCreate(
                    [
                        'run_id' => $this->run->id,
                        'email' => $email,
                    ],
                    [
                        'subscriber_id' => $subscriberId,
                        'success' => (bool) $success,
                        'result' => $normalizedResult,
                        'message' => $message,
                        'flags' => null,
                        'raw' => $r,
                        'validated_at' => now(),
                    ]
                );

                if (
                    $item
                    && $item->action_taken === 'none'
                    && $item->success
                    && $item->result === 'undeliverable'
                    && $subscriberId
                ) {
                    $action = (string) ($this->run->invalid_action ?? 'none');
                    $actionTaken = 'none';

                    if ($action === 'unsubscribe') {
                        $subscriberModel = ListSubscriber::find($subscriberId);
                        if ($subscriberModel) {
                            $subscriberService->unsubscribe($subscriberModel);
                            $actionTaken = 'unsubscribe';
                        }
                    } elseif ($action === 'mark_spam') {
                        $subscriberModel = ListSubscriber::find($subscriberId);
                        if ($subscriberModel) {
                            $subscriberService->unsubscribe($subscriberModel);
                            $subscriberModel->update([
                                'is_complained' => true,
                                'suppressed_at' => now(),
                            ]);

                            SuppressionList::firstOrCreate(
                                [
                                    'customer_id' => $customer->id,
                                    'email' => $email,
                                ],
                                [
                                    'reason' => 'complaint',
                                    'reason_description' => 'Marked as spam by email validation.',
                                    'subscriber_id' => $subscriberModel->id,
                                    'campaign_id' => null,
                                    'suppressed_at' => now(),
                                ]
                            );

                            $actionTaken = 'mark_spam';
                        }
                    } elseif ($action === 'delete') {
                        $subscriberModel = ListSubscriber::find($subscriberId);
                        if ($subscriberModel) {
                            $subscriberService->delete($subscriberModel);
                            $actionTaken = 'delete';
                        }
                    }

                    if ($actionTaken !== 'none') {
                        $item->update([
                            'action_taken' => $actionTaken,
                        ]);
                    }
                }
            }
        });

        $counts = EmailValidationRunItem::query()
            ->where('run_id', $this->run->id)
            ->selectRaw('COUNT(*) as processed')
            ->selectRaw("SUM(CASE WHEN success = 1 AND result = 'deliverable' THEN 1 ELSE 0 END) as deliverable")
            ->selectRaw("SUM(CASE WHEN success = 1 AND result = 'undeliverable' THEN 1 ELSE 0 END) as undeliverable")
            ->selectRaw("SUM(CASE WHEN success = 1 AND result = 'accept_all' THEN 1 ELSE 0 END) as accept_all")
            ->selectRaw("SUM(CASE WHEN success = 1 AND (result = 'unknown' OR result IS NULL) THEN 1 ELSE 0 END) as unknown")
            ->selectRaw('SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as errors')
            ->first();

        $this->run->refresh();
        $this->run->update([
            'processed_count' => (int) ($counts->processed ?? 0),
            'deliverable_count' => (int) ($counts->deliverable ?? 0),
            'undeliverable_count' => (int) ($counts->undeliverable ?? 0),
            'accept_all_count' => (int) ($counts->accept_all ?? 0),
            'unknown_count' => (int) ($counts->unknown ?? 0),
            'error_count' => (int) ($counts->errors ?? 0),
            'status' => 'completed',
            'finished_at' => now(),
        ]);

        Log::info('Email validation bulk run completed', [
            'run_id' => $this->run->id,
            'customer_id' => $customer->id,
        ]);
    }

    private function isRateLimitedPayload(array $payload): bool
    {
        $raw = $payload['raw'] ?? null;
        if (is_array($raw) && (int) ($raw['status'] ?? 0) === 429) {
            return true;
        }

        $message = $payload['message'] ?? null;
        $message = is_string($message) ? strtolower($message) : '';
        if ($message !== '' && (str_contains($message, '429') || str_contains($message, 'rate') || str_contains($message, 'limit'))) {
            return true;
        }

        return false;
    }

    private function isInsufficientCreditsPayload(array $payload): bool
    {
        $message = $payload['message'] ?? null;
        $message = is_string($message) ? strtolower($message) : '';

        $raw = $payload['raw'] ?? null;
        $status = is_array($raw) ? (int) ($raw['status'] ?? 0) : 0;
        $body = '';
        if (is_array($raw) && is_string($raw['body'] ?? null)) {
            $body = strtolower((string) $raw['body']);
        }

        if ($status !== 403 && $message === '' && $body === '') {
            return false;
        }

        if ($status !== 403 && !str_contains($message, '403') && !str_contains($body, '403')) {
            return false;
        }

        $haystack = trim($message . ' ' . $body);
        if ($haystack === '') {
            return false;
        }

        return str_contains($haystack, 'credit')
            || str_contains($haystack, 'credits')
            || str_contains($haystack, 'balance')
            || str_contains($haystack, 'billing')
            || str_contains($haystack, 'payment')
            || str_contains($haystack, 'insufficient')
            || str_contains($haystack, 'quota')
            || str_contains($haystack, 'limit exceeded');
    }

    private function pauseRunForInsufficientCredits(): void
    {
        $this->run->refresh();
        if ($this->run->status !== 'running') {
            return;
        }

        $settings = is_array($this->run->settings) ? $this->run->settings : [];
        $settings['paused_by'] = 'system';
        $settings['is_paused'] = true;
        $settings['pause_reason'] = 'Insufficient credits';
        unset($settings['auto_cycle_resume_at']);

        $this->run->update([
            'settings' => $settings,
        ]);

        Log::warning('Email validation bulk run paused due to insufficient credits (HTTP 403).', [
            'run_id' => $this->run->id,
        ]);
    }

    private function failRun(string $reason): void
    {
        $this->run->refresh();

        if (in_array($this->run->status, ['completed', 'failed'], true)) {
            return;
        }

        $this->run->update([
            'status' => 'failed',
            'finished_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Email validation bulk run job failed', [
            'run_id' => $this->run->id,
            'message' => $exception->getMessage(),
        ]);

        $this->failRun($exception->getMessage());
    }
}
