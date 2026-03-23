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
use Illuminate\Support\Str;

class ProcessEmailValidationChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 10;
    public int $backoff = 30;
    public int $timeout = 300;

    /**
     * @param  array<int>  $subscriberIds
     */
    public function __construct(
        public EmailValidationRun $run,
        public array $subscriberIds
    ) {
    }

    public function handle(
        SnapvalidClient $snapvalidClient,
        ListSubscriberService $subscriberService
    ): void {
        $this->run->refresh();

        $settings = is_array($this->run->settings) ? $this->run->settings : [];
        if ((bool) data_get($settings, 'is_paused', false)) {
            $this->release(30);
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

        $monthlyLimit = (int) $customer->groupSetting('email_validation.monthly_limit', 0);

        $now = Carbon::now();
        $periodStart = $now->copy()->startOfMonth()->toDateString();
        $periodEnd = $now->copy()->endOfMonth()->toDateString();

        $subscribers = ListSubscriber::query()
            ->where('list_id', $this->run->list_id)
            ->whereIn('id', $this->subscriberIds)
            ->get(['id', 'email'])
            ->keyBy('id');

        if ($subscribers->isEmpty()) {
            return;
        }

        $emails = $subscribers->values()->pluck('email')
            ->filter()
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->values()
            ->all();

        $existingItems = EmailValidationRunItem::query()
            ->where('run_id', $this->run->id)
            ->whereIn('email', $emails)
            ->get()
            ->keyBy(fn ($item) => strtolower(trim((string) $item->email)));

        $pauseCheck = 0;

        $startedAt = microtime(true);
        $maxSeconds = 40;

        $pendingIds = array_values(array_filter(array_map('intval', $this->subscriberIds), fn ($id) => $id > 0));
        $totalIds = count($pendingIds);

        $madeProgress = false;

        foreach ($pendingIds as $i => $subscriberId) {
            if ((microtime(true) - $startedAt) >= $maxSeconds) {
                $remaining = array_slice($pendingIds, $i);
                if (!empty($remaining)) {
                    self::dispatch($this->run, $remaining)
                        ->onQueue('email-validation');
                }
                return;
            }

            $pauseCheck++;
            if ($pauseCheck % 5 === 0) {
                $this->run->refresh();

                if ($madeProgress) {
                    $settings = is_array($this->run->settings) ? $this->run->settings : [];
                    $settings['last_progress_at'] = now()->toDateTimeString();
                    $this->run->update([
                        'settings' => $settings,
                    ]);
                }
                $settings = is_array($this->run->settings) ? $this->run->settings : [];
                if ((bool) data_get($settings, 'is_paused', false)) {
                    $this->release(30);
                    return;
                }
            }

            $subscriber = $subscribers->get($subscriberId);
            if (!$subscriber) {
                continue;
            }

            $email = strtolower(trim((string) $subscriber->email));
            if ($email === '') {
                continue;
            }

            $existingItem = $existingItems->get($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (!$existingItem) {
                    $item = EmailValidationRunItem::updateOrCreate(
                        [
                            'run_id' => $this->run->id,
                            'email' => $email,
                        ],
                        [
                            'subscriber_id' => $subscriber->id,
                            'success' => true,
                            'result' => 'undeliverable',
                            'message' => 'Invalid email format.',
                            'action_taken' => 'none',
                            'flags' => ['invalid_format' => true],
                            'raw' => ['source' => 'local_validation'],
                            'validated_at' => now(),
                        ]
                    );

                    $existingItem = $item;
                    $existingItems->put($email, $item);

                    if ($item->wasRecentlyCreated) {
                        EmailValidationRun::whereKey($this->run->id)->update([
                            'processed_count' => DB::raw('processed_count + 1'),
                            'undeliverable_count' => DB::raw('undeliverable_count + 1'),
                        ]);
                        $madeProgress = true;
                    }
                }
            } else {
                $success = null;
                $normalizedResult = null;
                $message = null;
                $flags = null;
                $raw = null;

                if ($existingItem) {
                    $success = (bool) $existingItem->success;
                    $normalizedResult = is_string($existingItem->result) ? $existingItem->result : null;
                    $message = $existingItem->message;
                    $flags = $existingItem->flags;
                    $raw = $existingItem->raw;
                } elseif ($normalizedResult === null) {
                    try {
                        if ($tool->provider === 'snapvalid') {
                            $verification = $snapvalidClient->verify((string) $tool->api_key, $email);
                        } else {
                            $verification = [
                                'success' => false,
                                'result' => null,
                                'message' => 'Unsupported provider.',
                                'flags' => null,
                                'raw' => null,
                            ];
                        }
                    } catch (\Throwable $e) {
                        $msg = $e->getMessage();
                        if (is_string($msg) && str_contains($msg, 'status code 429')) {
                            $this->releaseChunkDueToRateLimit(
                                $pendingIds,
                                $i,
                                (int) config('mailzen.email_validation.snapvalid_429_delay_seconds', 10)
                            );
                            return;
                        }

                        $verification = [
                            'success' => false,
                            'result' => null,
                            'message' => $msg,
                            'flags' => null,
                            'raw' => [
                                'exception' => $msg,
                            ],
                        ];
                    }

                    if ($this->isRateLimitedVerification($verification)) {
                        $this->releaseChunkDueToRateLimit(
                            $pendingIds,
                            $i,
                            (int) config('mailzen.email_validation.snapvalid_429_delay_seconds', 10)
                        );
                        return;
                    }

                    if ($this->isInsufficientCreditsVerification($verification)) {
                        $this->pauseRunForInsufficientCredits();
                        return;
                    }

                    $rawStatus = null;
                    $rawBody = null;
                    if (is_array($verification['raw'] ?? null)) {
                        $rawStatus = is_numeric($verification['raw']['status'] ?? null)
                            ? (int) $verification['raw']['status']
                            : null;
                        $rawBody = is_string($verification['raw']['body'] ?? null)
                            ? (string) $verification['raw']['body']
                            : null;
                    }

                    if ($rawStatus !== null && in_array($rawStatus, [400, 401, 403, 404, 422], true)) {
                        Log::warning('Email validation provider request failed', [
                            'run_id' => $this->run->id,
                            'tool_id' => $tool->id,
                            'provider' => $tool->provider,
                            'status' => $rawStatus,
                            'email_hash' => sha1($email),
                            'email_domain' => Str::contains($email, '@') ? Str::after($email, '@') : null,
                            'body_snippet' => is_string($rawBody) ? mb_substr($rawBody, 0, 500) : null,
                            'message' => $verification['message'] ?? null,
                        ]);
                    }

                    $apiSuccess = (bool) ($verification['success'] ?? false);
                    $result = is_string($verification['result'] ?? null) ? strtolower(trim($verification['result'])) : null;

                    $normalizedResult = match ($result) {
                        'deliverable' => 'deliverable',
                        'undeliverable' => 'undeliverable',
                        'accept_all', 'accept-all' => 'accept_all',
                        'unknown' => 'unknown',
                        default => ($apiSuccess ? 'unknown' : null),
                    };

                    $success = $normalizedResult !== null;

                    $message = is_string($verification['message'] ?? null) ? $verification['message'] : null;
                    $flags = is_array($verification['flags'] ?? null) ? $verification['flags'] : null;
                    $raw = is_array($verification['raw'] ?? null) ? $verification['raw'] : null;

                    $fullMessage = $message;
                    if (is_string($message) && mb_strlen($message) > 255) {
                        $message = mb_substr($message, 0, 255);
                        $raw = is_array($raw) ? $raw : [];
                        $raw['message_full'] = $fullMessage;
                    }

                    $detectText = strtolower(trim((string) ($message ?? '')));
                    if ($detectText !== '') {
                        $isInboxFull = str_contains($detectText, '452 4.2.2')
                            || str_contains($detectText, '452-4.2.2')
                            || str_contains($detectText, 'overquotatemp')
                            || str_contains($detectText, 'out of storage space')
                            || str_contains($detectText, 'inbox is out of storage')
                            || str_contains($detectText, 'mailbox full')
                            || str_contains($detectText, 'storage quota');

                        if ($isInboxFull) {
                            $flags = is_array($flags) ? $flags : [];
                            $flags['inbox_full'] = true;
                            $flags['inbox_full_reason'] = mb_substr((string) $message, 0, 500);
                        }
                    }

                    $actionTaken = 'none';
                    $deliverableDelta = $normalizedResult === 'deliverable' ? 1 : 0;
                    $undeliverableDelta = $normalizedResult === 'undeliverable' ? 1 : 0;
                    $acceptAllDelta = $normalizedResult === 'accept_all' ? 1 : 0;
                    $unknownDelta = $normalizedResult === 'unknown' ? 1 : 0;
                    $errorDelta = $normalizedResult === null ? 1 : 0;

                    $createdItem = null;
                    try {
                        DB::transaction(function () use (
                            $subscriber,
                            $email,
                            $success,
                            $normalizedResult,
                            $message,
                            $flags,
                            $raw,
                            $actionTaken,
                            $customer,
                            $monthlyLimit,
                            $periodStart,
                            $periodEnd,
                            $deliverableDelta,
                            $undeliverableDelta,
                            $acceptAllDelta,
                            $unknownDelta,
                            $errorDelta,
                            &$createdItem
                        ) {
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

                            if ($monthlyLimit > 0 && ((int) $log->amount + 1) > $monthlyLimit) {
                                throw new \RuntimeException('Monthly validation limit exceeded.');
                            }

                            $log->increment('amount', 1);
                            $log->update([
                                'context' => array_merge($log->context ?? [], ['run_id' => $this->run->id]),
                            ]);

                            $createdItem = EmailValidationRunItem::create([
                                'run_id' => $this->run->id,
                                'subscriber_id' => $subscriber->id,
                                'email' => $email,
                                'success' => (bool) $success,
                                'result' => $normalizedResult,
                                'message' => $message,
                                'action_taken' => $actionTaken,
                                'flags' => $flags,
                                'raw' => $raw,
                                'validated_at' => now(),
                            ]);

                            EmailValidationRun::whereKey($this->run->id)->update([
                                'processed_count' => DB::raw('processed_count + 1'),
                                'deliverable_count' => DB::raw('deliverable_count + ' . (int) $deliverableDelta),
                                'undeliverable_count' => DB::raw('undeliverable_count + ' . (int) $undeliverableDelta),
                                'accept_all_count' => DB::raw('accept_all_count + ' . (int) $acceptAllDelta),
                                'unknown_count' => DB::raw('unknown_count + ' . (int) $unknownDelta),
                                'error_count' => DB::raw('error_count + ' . (int) $errorDelta),
                            ]);
                        });
                    } catch (\Throwable $e) {
                        if ($e instanceof \Illuminate\Database\QueryException) {
                            $sqlState = (string) ($e->errorInfo[0] ?? '');
                            $driverCode = (string) ($e->errorInfo[1] ?? '');
                            $msg = $e->getMessage();

                            if (
                                $sqlState === '22001'
                                && (
                                    str_contains($driverCode, '1406')
                                    || str_contains($msg, 'Data too long for column')
                                    || str_contains($msg, "for column 'message'")
                                )
                            ) {
                                $safeMessage = is_string($message) ? mb_substr($message, 0, 190) : null;
                                $safeRaw = is_array($raw) ? $raw : [];
                                if (is_string($fullMessage) && $fullMessage !== '') {
                                    $safeRaw['message_full'] = $fullMessage;
                                }

                                try {
                                    DB::transaction(function () use (
                                        $subscriber,
                                        $email,
                                        $success,
                                        $normalizedResult,
                                        $safeMessage,
                                        $flags,
                                        $safeRaw,
                                        $actionTaken,
                                        $customer,
                                        $monthlyLimit,
                                        $periodStart,
                                        $periodEnd,
                                        $deliverableDelta,
                                        $undeliverableDelta,
                                        $acceptAllDelta,
                                        $unknownDelta,
                                        $errorDelta,
                                        &$createdItem
                                    ) {
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

                                        if ($monthlyLimit > 0 && ((int) $log->amount + 1) > $monthlyLimit) {
                                            throw new \RuntimeException('Monthly validation limit exceeded.');
                                        }

                                        $log->increment('amount', 1);
                                        $log->update([
                                            'context' => array_merge($log->context ?? [], ['run_id' => $this->run->id]),
                                        ]);

                                        $createdItem = EmailValidationRunItem::create([
                                            'run_id' => $this->run->id,
                                            'subscriber_id' => $subscriber->id,
                                            'email' => $email,
                                            'success' => (bool) $success,
                                            'result' => $normalizedResult,
                                            'message' => $safeMessage,
                                            'action_taken' => $actionTaken,
                                            'flags' => $flags,
                                            'raw' => $safeRaw,
                                            'validated_at' => now(),
                                        ]);

                                        EmailValidationRun::whereKey($this->run->id)->update([
                                            'processed_count' => DB::raw('processed_count + 1'),
                                            'deliverable_count' => DB::raw('deliverable_count + ' . (int) $deliverableDelta),
                                            'undeliverable_count' => DB::raw('undeliverable_count + ' . (int) $undeliverableDelta),
                                            'accept_all_count' => DB::raw('accept_all_count + ' . (int) $acceptAllDelta),
                                            'unknown_count' => DB::raw('unknown_count + ' . (int) $unknownDelta),
                                            'error_count' => DB::raw('error_count + ' . (int) $errorDelta),
                                        ]);
                                    });

                                    $existingItem = $createdItem;
                                    $existingItems->put($email, $createdItem);
                                    continue;
                                } catch (\Throwable $e2) {
                                    Log::warning('Email validation run item insert failed (message too long)', [
                                        'run_id' => $this->run->id,
                                        'email' => $email,
                                        'message_length' => is_string($fullMessage) ? mb_strlen($fullMessage) : null,
                                        'error' => $e2->getMessage(),
                                    ]);

                                    EmailValidationRun::whereKey($this->run->id)->update([
                                        'processed_count' => DB::raw('processed_count + 1'),
                                        'unknown_count' => DB::raw('unknown_count + 1'),
                                    ]);

                                    continue;
                                }
                            }

                            if (
                                $sqlState === '23000'
                                && (
                                    str_contains($driverCode, '1062')
                                    || str_contains($msg, 'email_validation_run_items_run_id_email_unique')
                                    || str_contains($msg, 'Duplicate entry')
                                )
                            ) {
                                $existingItem = EmailValidationRunItem::query()
                                    ->where('run_id', $this->run->id)
                                    ->where('email', $email)
                                    ->first();

                                if ($existingItem) {
                                    $existingItems->put($email, $existingItem);
                                    continue;
                                }
                            }
                        }

                        $this->failRun($e->getMessage());
                        break;
                    }

                    $existingItem = $createdItem;
                    $existingItems->put($email, $createdItem);
                    $madeProgress = true;
                }
            }

            if (
                $existingItem
                && $existingItem->action_taken === 'none'
                && $existingItem->success
                && $existingItem->result === 'undeliverable'
            ) {
                $action = (string) ($this->run->invalid_action ?? 'none');
                $actionTaken = 'none';

                if ($action === 'unsubscribe') {
                    $subscriberModel = ListSubscriber::find($subscriber->id);
                    if ($subscriberModel) {
                        $subscriberService->unsubscribe($subscriberModel);
                        $actionTaken = 'unsubscribe';
                    }
                } elseif ($action === 'mark_spam') {
                    $subscriberModel = ListSubscriber::find($subscriber->id);
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
                    $subscriberModel = ListSubscriber::find($subscriber->id);
                    if ($subscriberModel) {
                        $subscriberService->delete($subscriberModel);
                        $actionTaken = 'delete';
                    }
                }

                if ($actionTaken !== 'none') {
                    $existingItem->update([
                        'action_taken' => $actionTaken,
                    ]);
                }
            }
        }

        $this->run->refresh();

        if ($madeProgress) {
            $settings = is_array($this->run->settings) ? $this->run->settings : [];
            $settings['last_progress_at'] = now()->toDateTimeString();
            $this->run->update([
                'settings' => $settings,
            ]);
        }

        if ($this->run->status === 'running' && $this->run->processed_count >= $this->run->total_emails) {
            $this->run->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);
        }
    }

    private function isRateLimitedVerification(array $verification): bool
    {
        $raw = $verification['raw'] ?? null;
        if (is_array($raw) && (int) ($raw['status'] ?? 0) === 429) {
            return true;
        }

        $message = $verification['message'] ?? null;
        $message = is_string($message) ? strtolower($message) : '';
        if ($message !== '' && (str_contains($message, '429') || str_contains($message, 'too many requests') || str_contains($message, 'rate limit'))) {
            return true;
        }

        return false;
    }

    private function isInsufficientCreditsVerification(array $verification): bool
    {
        $message = $verification['message'] ?? null;
        $message = is_string($message) ? strtolower($message) : '';

        $raw = $verification['raw'] ?? null;
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

        Log::warning('Email validation paused due to insufficient credits (HTTP 403).', [
            'run_id' => $this->run->id,
        ]);
    }

    /**
     * Re-dispatches the remaining subscriber IDs with a delay and exits without
     * incrementing run stats (so rate limiting doesn't count as an error).
     *
     * @param  array<int>  $pendingIds
     */
    private function releaseChunkDueToRateLimit(array $pendingIds, int $currentIndex, int $delaySeconds): void
    {
        $delaySeconds = max(1, $delaySeconds);
        $remaining = array_slice($pendingIds, max(0, $currentIndex));

        Log::warning('Email validation rate limited (HTTP 429). Rescheduling remaining chunk.', [
            'run_id' => $this->run->id,
            'delay_seconds' => $delaySeconds,
            'remaining_count' => count($remaining),
        ]);

        if (!empty($remaining)) {
            self::dispatch($this->run, $remaining)
                ->delay(now()->addSeconds($delaySeconds))
                ->onQueue('email-validation');
        }
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
        Log::error('Email validation chunk job failed', [
            'run_id' => $this->run->id,
            'message' => $exception->getMessage(),
        ]);

        $this->failRun($exception->getMessage());
    }
}
