<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Jobs\SendCampaignChunkJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResumeCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Campaign $campaign
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh campaign to get latest status
        $this->campaign->refresh();

        // Check if campaign is still paused and can be resumed
        if (!$this->campaign->isPaused()) {
            Log::info("Campaign {$this->campaign->id} is not paused. Skipping resume.");
            return;
        }

        try {
            // Get pending recipients
            $pendingCount = $this->campaign->recipients()
                ->where('status', 'pending')
                ->count();

            if ($pendingCount === 0) {
                Log::info("Campaign {$this->campaign->id} has no pending recipients. Marking as completed.");
                $this->campaign->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
                return;
            }

            // Resume the campaign
            $this->campaign->update([
                'status' => 'running',
                'failure_reason' => null, // Clear the rate limit error
            ]);

            Log::info("Resumed campaign {$this->campaign->id} with {$pendingCount} pending recipients");

            // Dispatch chunks for remaining recipients
            $chunkSize = 50;
            $this->campaign->recipients()
                ->where('status', 'pending')
                ->chunk($chunkSize, function ($recipients) {
                    SendCampaignChunkJob::dispatch($this->campaign, $recipients->pluck('id')->toArray())
                        ->onQueue('campaigns');
                });

            Log::info("Dispatched chunks for resumed campaign {$this->campaign->id}");

        } catch (\Exception $e) {
            Log::error("Failed to resume campaign {$this->campaign->id}: " . $e->getMessage());
            
            // Keep campaign paused if resume fails
            $this->campaign->update([
                'failure_reason' => 'Failed to resume: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ResumeCampaignJob failed for campaign {$this->campaign->id}: " . $exception->getMessage());
        
        // Keep campaign paused on failure
        $this->campaign->update([
            'failure_reason' => 'Resume failed: ' . $exception->getMessage(),
        ]);
    }
}
