<?php

namespace App\Console\Commands;

use App\Models\EmailValidationRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoPauseResumeEmailValidations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email-validation:auto-pause-resume {--run-seconds=180} {--pause-seconds=10} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically pause and resume email validation runs to keep large runs progressing';

    public function handle(): int
    {
        $runSeconds = (int) $this->option('run-seconds');
        $pauseSeconds = (int) $this->option('pause-seconds');
        $dryRun = (bool) $this->option('dry-run');

        $runSeconds = max(30, min(3600, $runSeconds));
        $pauseSeconds = max(1, min(300, $pauseSeconds));

        $now = now();

        $this->info("Auto-cycling email validation runs (run={$runSeconds}s, pause={$pauseSeconds}s)...");

        $autoPausedRuns = EmailValidationRun::query()
            ->where('status', 'running')
            ->whereNotNull('settings')
            ->where('settings->paused_by', 'auto')
            ->where('settings->is_paused', true)
            ->get();

        foreach ($autoPausedRuns as $run) {
            $settings = is_array($run->settings) ? $run->settings : [];
            $resumeAt = data_get($settings, 'auto_cycle_resume_at');

            if (!$resumeAt) {
                continue;
            }

            try {
                $resumeAtTs = $resumeAt ? \Carbon\Carbon::parse($resumeAt) : null;
            } catch (\Throwable $e) {
                $resumeAtTs = null;
            }

            if (!$resumeAtTs || $resumeAtTs->greaterThan($now)) {
                continue;
            }

            $this->line("- Resuming run #{$run->id}");

            if ($dryRun) {
                continue;
            }

            $settings['paused_by'] = null;
            $settings['is_paused'] = false;
            $settings['auto_cycle_last_resume_at'] = $now->toDateTimeString();
            unset($settings['auto_cycle_resume_at']);

            $run->update([
                'settings' => $settings,
            ]);

            Log::info('Auto-resumed email validation run', [
                'run_id' => $run->id,
                'paused_by' => 'auto',
            ]);
        }

        $threshold = $now->copy()->subSeconds($runSeconds);

        $runningRuns = EmailValidationRun::query()
            ->where('status', 'running')
            ->whereNotNull('started_at')
            ->where('started_at', '<=', $threshold)
            ->whereColumn('processed_count', '<', 'total_emails')
            ->get();

        foreach ($runningRuns as $run) {
            $settings = is_array($run->settings) ? $run->settings : [];

            $lastProgressAt = data_get($settings, 'last_progress_at');
            if (is_string($lastProgressAt) && trim($lastProgressAt) !== '') {
                try {
                    $lastProgressAtTs = \Carbon\Carbon::parse($lastProgressAt);
                    if ($lastProgressAtTs->greaterThan($threshold)) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // ignore parse errors
                }
            }

            $lastPauseAt = data_get($settings, 'auto_cycle_last_pause_at');
            if ($lastPauseAt) {
                try {
                    $lastPauseAtTs = \Carbon\Carbon::parse($lastPauseAt);
                    if ($lastPauseAtTs->greaterThan($threshold)) {
                        continue;
                    }
                } catch (\Throwable $e) {
                    // ignore parse errors
                }
            }

            $this->line("- Pausing run #{$run->id}");

            if ($dryRun) {
                continue;
            }

            $settings['paused_by'] = 'auto';
            $settings['is_paused'] = true;
            $settings['auto_cycle_last_pause_at'] = $now->toDateTimeString();
            $settings['auto_cycle_resume_at'] = $now->copy()->addSeconds($pauseSeconds)->toDateTimeString();

            $run->update([
                'settings' => $settings,
            ]);

            Log::info('Auto-paused email validation run', [
                'run_id' => $run->id,
                'run_seconds' => $runSeconds,
                'pause_seconds' => $pauseSeconds,
            ]);
        }

        return Command::SUCCESS;
    }
}
