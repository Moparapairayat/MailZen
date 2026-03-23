<?php

namespace App\Console\Commands;

use App\Jobs\SendAutoResponderStepJob;
use App\Models\AutoResponderRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessAutoResponderRuns extends Command
{
    protected $signature = 'autoresponders:process-due {--limit=200}';

    protected $description = 'Dispatch queued jobs for due autoresponder steps';

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?? 200);

        $dueRuns = AutoResponderRun::query()
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
                $claimed = AutoResponderRun::query()
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

                SendAutoResponderStepJob::dispatch($run->id)->onQueue('autoresponders');
                $dispatched++;
            } catch (\Throwable $e) {
                Log::error('Failed to dispatch autoresponder run', [
                    'run_id' => $run->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Dispatched {$dispatched} autoresponder step job(s).");

        return Command::SUCCESS;
    }
}
