<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Jobs\SendCampaignChunkJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoPauseResumeCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:auto-pause-resume {--seconds=60} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically pause and resume campaigns after specified seconds to prevent stalling';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $seconds = $this->option('seconds');
        $dryRun = $this->option('dry-run');
        
        $threshold = now()->subSeconds($seconds);
        
        $this->info("Checking for campaigns running for more than {$seconds} seconds...");
        
        // Find campaigns that have been running for more than X seconds
        $campaigns = Campaign::where('status', 'running')
            ->where('started_at', '<=', $threshold)
            ->get();
            
        if ($campaigns->isEmpty()) {
            $this->info("No campaigns found running for more than {$seconds} seconds.");
            return 0;
        }
        
        $this->info("Found {$campaigns->count()} campaign(s) to pause and resume:");
        
        foreach ($campaigns as $campaign) {
            $runningSeconds = $campaign->started_at->diffInSeconds(now());
            $this->line("- Campaign #{$campaign->id}: '{$campaign->name}' (running for {$runningSeconds} seconds)");
            
            if ($dryRun) {
                $this->line("  [DRY RUN] Would pause and resume campaign #{$campaign->id}");
                continue;
            }
            
            try {
                // Pause the campaign
                $pauseTime = now();
                $campaign->update(['status' => 'paused']);
                Log::info("Auto-paused campaign #{$campaign->id} to prevent stalling", [
                    'campaign_id' => $campaign->id,
                    'campaign_name' => $campaign->name,
                    'running_seconds' => $runningSeconds,
                    'paused_at' => $pauseTime->toIso8601String(),
                    'started_at' => $campaign->started_at->toIso8601String(),
                ]);
                
                // Get pending recipients
                $pendingRecipients = $campaign->recipients()
                    ->where('status', 'pending')
                    ->pluck('id')
                    ->toArray();
                
                // Get total recipients to determine if campaign ever had recipients
                $totalRecipients = $campaign->recipients()->count();
                    
                if (!empty($pendingRecipients)) {
                    // Resume by dispatching new chunks
                    $resumeTime = now();
                    $chunks = array_chunk($pendingRecipients, 50);
                    foreach ($chunks as $chunk) {
                        SendCampaignChunkJob::dispatch($campaign, $chunk)
                            ->onQueue('campaigns');
                    }
                    
                    // Update campaign status back to running
                    $campaign->update(['status' => 'running']);
                    
                    $pauseDuration = $pauseTime->diffInSeconds($resumeTime);
                    Log::info("Auto-resumed campaign #{$campaign->id} with fresh chunks", [
                        'campaign_id' => $campaign->id,
                        'campaign_name' => $campaign->name,
                        'pending_recipients' => count($pendingRecipients),
                        'chunks_dispatched' => count($chunks),
                        'paused_at' => $pauseTime->toIso8601String(),
                        'resumed_at' => $resumeTime->toIso8601String(),
                        'pause_duration_seconds' => $pauseDuration,
                        'total_running_seconds' => $runningSeconds,
                    ]);
                    
                    $this->line("  ✓ Paused and resumed with " . count($chunks) . " chunks (" . count($pendingRecipients) . " recipients) - Pause duration: {$pauseDuration}s");
                } else {
                    // No pending recipients - check if campaign ever had recipients
                    if ($totalRecipients === 0) {
                        // Campaign never had recipients - this is a failure
                        $campaign->update([
                            'status' => 'failed',
                            'finished_at' => now(),
                            'failure_reason' => 'No recipients were ever created. Check email list for confirmed subscribers.',
                        ]);
                        
                        Log::warning("Auto-failed campaign #{$campaign->id} (no recipients ever created)", [
                            'campaign_id' => $campaign->id,
                            'campaign_name' => $campaign->name,
                            'paused_at' => $pauseTime->toIso8601String(),
                            'failed_at' => now()->toIso8601String(),
                            'total_running_seconds' => $runningSeconds,
                        ]);
                        
                        $this->line("  ✗ No recipients ever created, marked as failed");
                    } else {
                        // Campaign had recipients but none are pending - it's actually complete
                        $sentCount = $campaign->recipients()->where('status', 'sent')->count();
                        $failedCount = $campaign->recipients()->where('status', 'failed')->count();
                        
                        $campaign->update([
                            'status' => 'completed',
                            'finished_at' => now(),
                        ]);
                        
                        Log::info("Auto-completed campaign #{$campaign->id} (no pending recipients)", [
                            'campaign_id' => $campaign->id,
                            'campaign_name' => $campaign->name,
                            'total_recipients' => $totalRecipients,
                            'sent_count' => $sentCount,
                            'failed_count' => $failedCount,
                            'paused_at' => $pauseTime->toIso8601String(),
                            'completed_at' => now()->toIso8601String(),
                            'total_running_seconds' => $runningSeconds,
                        ]);
                        
                        $this->line("  ✓ No pending recipients, marked as completed (Total: {$totalRecipients}, Sent: {$sentCount}, Failed: {$failedCount})");
                    }
                }
                
            } catch (\Exception $e) {
                Log::error("Failed to auto pause-resume campaign #{$campaign->id}: " . $e->getMessage(), [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
                
                $this->error("  ✗ Failed: " . $e->getMessage());
            }
        }
        
        return 0;
    }
}
