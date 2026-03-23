<?php

namespace App\Jobs;

use App\Models\EmailWarmup;
use App\Services\EmailWarmupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWarmupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;
    public int $timeout = 600;

    public function __construct(
        public int $warmupId
    ) {}

    public function handle(EmailWarmupService $warmupService): void
    {
        $warmup = EmailWarmup::find($this->warmupId);

        if (!$warmup) {
            Log::warning('Warmup not found for processing', [
                'warmup_id' => $this->warmupId,
            ]);
            return;
        }

        if (!$warmup->isActive()) {
            Log::info('Warmup is not active, skipping processing', [
                'warmup_id' => $this->warmupId,
                'status' => $warmup->status,
            ]);
            return;
        }

        try {
            $warmupService->processDay($warmup);
        } catch (\Exception $e) {
            Log::error('Failed to process warmup day', [
                'warmup_id' => $this->warmupId,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $warmup->update([
                    'status' => 'failed',
                ]);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessWarmupJob failed permanently', [
            'warmup_id' => $this->warmupId,
            'error' => $exception->getMessage(),
        ]);

        $warmup = EmailWarmup::find($this->warmupId);
        if ($warmup) {
            $warmup->update(['status' => 'failed']);
        }
    }
}
