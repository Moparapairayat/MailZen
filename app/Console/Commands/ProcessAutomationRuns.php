<?php

namespace App\Console\Commands;

use App\Jobs\ProcessAutomationRunJob;
use App\Models\AutomationRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutomationRuns extends Command
{
    protected $signature = 'automations:process-due {--limit=200}';

    protected $description = 'Dispatch queued jobs for due automation runs';

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?? 200);

        $dueRuns = AutomationRun::query()
            ->where('status', 'active')
            ->whereNotNull('next_scheduled_for')
            ->where('next_scheduled_for', '<=', now())
            ->where(function ($q) {
                $q->whereNull('locked_at')
                    ->orWhere('locked_at', '<=', now()->subMinutes(10));
            })
            ->orderBy('next_scheduled_for')
            ->limit($limit)
            ->get();

        if ($dueRuns->isEmpty()) {
            return Command::SUCCESS;
        }

        $dispatched = 0;

        foreach ($dueRuns as $run) {
            try {
                $claimed = AutomationRun::query()
                    ->whereKey($run->id)
                    ->where(function ($q) {
                        $q->whereNull('locked_at')
                            ->orWhere('locked_at', '<=', now()->subMinutes(10));
                    })
                    ->update([
                        'locked_at' => now(),
                    ]);

                if ($claimed !== 1) {
                    continue;
                }

                ProcessAutomationRunJob::dispatch($run->id)->onQueue('automations');
                $dispatched++;
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch automation run', [
                    'run_id' => $run->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Dispatched {$dispatched} automation run job(s).");

        return Command::SUCCESS;
    }
}
