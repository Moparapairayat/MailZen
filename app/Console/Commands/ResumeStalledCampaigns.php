<?php

namespace App\Console\Commands;

use App\Jobs\SendCampaignChunkJob;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResumeStalledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:resume-stalled
                            {--minutes=10 : Consider a campaign stalled if no recipient was sent in this many minutes}
                            {--dry-run : Only report stalled campaigns without re-dispatching}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect running campaigns that have stalled and re-dispatch their pending recipient chunks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $stallMinutes = (int) $this->option('minutes');
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Checking for campaigns stalled for more than {$stallMinutes} minute(s)...");

        // Find campaigns that are "running" but have pending recipients
        // and whose last sent_at timestamp is older than the threshold
        $runningCampaigns = Campaign::where('status', 'running')
            ->whereNotNull('started_at')
            ->get();

        if ($runningCampaigns->isEmpty()) {
            $this->info('No running campaigns found.');
            return Command::SUCCESS;
        }

        $stalledCount = 0;
        $threshold = now()->subMinutes($stallMinutes);

        foreach ($runningCampaigns as $campaign) {
            $pendingCount = $campaign->recipients()
                ->where('status', 'pending')
                ->count();

            if ($pendingCount === 0) {
                // No pending recipients — campaign should complete
                $campaign->checkCompletion();
                continue;
            }

            // Check the most recent sent_at among recipients
            $lastSentAt = $campaign->recipients()
                ->whereNotNull('sent_at')
                ->max('sent_at');

            // Also check if there are any jobs for this campaign still in the queue
            $pendingJobs = DB::table('jobs')
                ->where('queue', 'campaigns')
                ->where('payload', 'like', '%"campaignId":' . $campaign->id . '%')
                ->orWhere('payload', 'like', '%Campaign\\\\";s:' . strlen((string) $campaign->id) . ':\\"' . $campaign->id . '%')
                ->count();

            // Determine if stalled: no recent sends AND no pending queue jobs
            $isStalled = false;
            $stallReason = '';

            if ($lastSentAt === null) {
                // Never sent anything — stalled if started long ago
                if ($campaign->started_at->lt($threshold)) {
                    $isStalled = true;
                    $stallReason = 'No emails ever sent, started ' . $campaign->started_at->diffForHumans();
                }
            } elseif (\Carbon\Carbon::parse($lastSentAt)->lt($threshold)) {
                $isStalled = true;
                $stallReason = 'Last email sent ' . \Carbon\Carbon::parse($lastSentAt)->diffForHumans();
            }

            if (!$isStalled) {
                continue;
            }

            $stalledCount++;
            $totalRecipients = $campaign->recipients()->count();

            $this->warn("STALLED: Campaign #{$campaign->id} \"{$campaign->name}\"");
            $this->line("  Reason: {$stallReason}");
            $this->line("  Progress: " . ($totalRecipients - $pendingCount) . " / {$totalRecipients} sent, {$pendingCount} pending");
            $this->line("  Queue jobs found: {$pendingJobs}");

            if ($dryRun) {
                $this->line("  [DRY RUN] Would re-dispatch {$pendingCount} pending recipients.");
                continue;
            }

            // Sync stats first
            $campaign->syncStats();

            // Re-dispatch pending recipients in chunks
            $chunks = $campaign->recipients()
                ->where('status', 'pending')
                ->pluck('id')
                ->chunk(50);

            $chunkCount = 0;
            foreach ($chunks as $chunk) {
                SendCampaignChunkJob::dispatch($campaign, $chunk->toArray())
                    ->onQueue('campaigns');
                $chunkCount++;
            }

            Log::info("Resumed stalled campaign #{$campaign->id}: re-dispatched {$chunkCount} chunk(s) for {$pendingCount} pending recipients", [
                'campaign_id' => $campaign->id,
                'pending_count' => $pendingCount,
                'chunk_count' => $chunkCount,
                'stall_reason' => $stallReason,
            ]);

            $this->info("  Re-dispatched {$chunkCount} chunk(s) for {$pendingCount} pending recipients.");
        }

        if ($stalledCount === 0) {
            $this->info('No stalled campaigns found.');
        } else {
            $this->info("Found {$stalledCount} stalled campaign(s)." . ($dryRun ? ' (dry run — no action taken)' : ''));
        }

        return Command::SUCCESS;
    }
}
