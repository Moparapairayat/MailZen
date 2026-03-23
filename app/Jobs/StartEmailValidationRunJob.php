<?php

namespace App\Jobs;

use App\Models\EmailValidationRun;
use App\Jobs\ProcessEmailValidationChunkJob;
use App\Services\Billing\UsageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartEmailValidationRunJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public EmailValidationRun $run
    ) {
    }

    public function handle(): void
    {
        $this->run->refresh();

        $claimed = DB::transaction(function () {
            return EmailValidationRun::whereKey($this->run->id)
                ->where('status', 'pending')
                ->whereNull('started_at')
                ->update([
                    'status' => 'running',
                    'started_at' => now(),
                    'finished_at' => null,
                    'failure_reason' => null,
                ]);
        });

        if ($claimed !== 1) {
            return;
        }

        $this->run->refresh();
        $this->run->loadMissing(['customer', 'tool']);

        $customer = $this->run->customer;
        if (!$customer) {
            $this->run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'failure_reason' => 'Customer not found.',
            ]);
            return;
        }

        $tool = $this->run->tool;
        if (!$tool || !$tool->active) {
            $this->run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'failure_reason' => 'Email validation tool is missing or inactive.',
            ]);
            return;
        }

        $baseQuery = DB::table('list_subscribers')
            ->where('list_id', $this->run->list_id)
            ->whereNull('deleted_at')
            ->whereNotNull('email')
            ->where('email', '!=', '');

        $total = (clone $baseQuery)
            ->distinct()
            ->count('email');

        $this->run->update([
            'total_emails' => (int) $total,
        ]);

        if ($total <= 0) {
            $this->run->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);
            return;
        }

        $monthlyLimit = (int) $customer->groupSetting('email_validation.monthly_limit', 0);
        if ($monthlyLimit > 0) {
            $usage = app(UsageService::class)->getUsage($customer);
            $current = (int) ($usage['email_validation_emails_this_month'] ?? 0);
            $remaining = $monthlyLimit - $current;

            if ($remaining < $total) {
                $this->run->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'failure_reason' => "Monthly validation limit exceeded. Remaining: {$remaining}.",
                ]);
                return;
            }
        }

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

        Log::info('Email validation run started', [
            'run_id' => $this->run->id,
            'customer_id' => $customer->id,
            'total' => $total,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        $this->run->refresh();

        if (in_array($this->run->status, ['completed', 'failed'], true)) {
            return;
        }

        $this->run->update([
            'status' => 'failed',
            'finished_at' => now(),
            'failure_reason' => $exception->getMessage(),
        ]);
    }
}
