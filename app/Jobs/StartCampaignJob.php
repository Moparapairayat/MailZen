<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\SuppressionList;
use App\Jobs\SendCampaignChunkJob;
use App\Services\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StartCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // 60 seconds between retries
    public int $timeout = 900; // 15 minutes timeout for very large campaigns

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

        // Early exit for campaigns that are already finished
        if (in_array($this->campaign->status, ['completed', 'failed'], true)) {
            Log::info("Campaign {$this->campaign->id} is already {$this->campaign->status}. Skipping start job.");
            return;
        }

        try {
            app(CampaignService::class)->ensureCanRun($this->campaign);
        } catch (\RuntimeException $e) {
            $this->campaign->update([
                'status' => 'failed',
                'finished_at' => now(),
                'failure_reason' => $e->getMessage(),
            ]);
            return;
        }

        if ($this->campaign->scheduled_at && $this->campaign->scheduled_at->isFuture()) {
            $delaySeconds = max(1, now()->diffInSeconds($this->campaign->scheduled_at, false));
            $this->release($delaySeconds);
            return;
        }

        // Check if campaign can still be started
        if (!in_array($this->campaign->status, ['draft', 'scheduled', 'queued'], true)) {
            Log::warning("Campaign {$this->campaign->id} cannot be started. Current status: {$this->campaign->status}");
            // If campaign is already completed, don't treat this as an error
            if (in_array($this->campaign->status, ['completed', 'failed'], true)) {
                Log::info("Campaign {$this->campaign->id} is already {$this->campaign->status}. Skipping start job.");
                return;
            }
            return;
        }

        $claimed = DB::transaction(function () {
            return Campaign::whereKey($this->campaign->id)
                ->whereIn('status', ['draft', 'scheduled', 'queued'])
                ->whereNull('started_at')
                ->update([
                    'status' => 'running',
                    'started_at' => now(),
                ]);
        });

        if ($claimed !== 1) {
            Log::warning("Campaign {$this->campaign->id} start was skipped (already started or status changed).", [
                'status' => $this->campaign->status,
                'started_at' => $this->campaign->started_at,
            ]);
            return;
        }

        $this->campaign->refresh();

        try {
            // Prepare recipients if not already prepared
            $this->prepareRecipients();

            // Get pending recipients count
            $pendingCount = $this->campaign->recipients()
                ->where('status', 'pending')
                ->count();

            if ($pendingCount === 0) {
            $totalRecipients = $this->campaign->recipients()->count();
            
            if ($totalRecipients === 0) {
                // No recipients were ever created - this is a failure
                Log::warning("Campaign {$this->campaign->id} has no recipients to send to");
                $this->campaign->update([
                    'status' => 'failed',
                    'finished_at' => now(),
                    'failure_reason' => 'No eligible recipients found. Check if email list has confirmed subscribers.',
                ]);
            } else {
                // Recipients exist but none are pending - campaign is actually complete
                $sentCount = $this->campaign->recipients()->where('status', 'sent')->count();
                $failedCount = $this->campaign->recipients()->where('status', 'failed')->count();
                
                Log::info("Campaign {$this->campaign->id} has no pending recipients. Total: {$totalRecipients}, Sent: {$sentCount}, Failed: {$failedCount}");
                $this->campaign->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
            }
            return;
        }

            // Dispatch chunks (50-100 recipients per chunk)
            $chunkSize = 50;
            $this->campaign->recipients()
                ->where('status', 'pending')
                ->chunk($chunkSize, function ($recipients) {
                    SendCampaignChunkJob::dispatch($this->campaign, $recipients->pluck('id')->toArray())
                        ->onQueue('campaigns');
                });

            Log::info("Campaign {$this->campaign->id} started. Dispatched chunks for {$pendingCount} recipients");

        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            Log::error("Failed to start campaign {$this->campaign->id}: " . $errorMessage, [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->campaign->update([
                'status' => 'failed',
                'finished_at' => now(),
                'failure_reason' => $errorMessage,
            ]);

            throw $e;
        }
    }

    /**
     * Prepare recipients from email list.
     */
    protected function prepareRecipients(): void
    {
        // Check if recipients already exist
        if ($this->campaign->recipients()->count() > 0) {
            return;
        }

        if (!$this->campaign->emailList) {
            throw new \RuntimeException("Campaign {$this->campaign->id} has no associated email list");
        }

        // Get confirmed subscribers from the email list
        // Exclude bounced, complained, and suppressed subscribers
        $subscribers = $this->campaign->emailList->subscribers()
            ->where('status', 'confirmed')
            ->where('is_bounced', false)
            ->where('is_complained', false)
            ->whereNull('suppressed_at')
            ->get();

        // Also check global suppression list
        $suppressedEmails = SuppressionList::where('customer_id', $this->campaign->customer_id)
            ->pluck('email')
            ->toArray();

        if (!empty($suppressedEmails)) {
            $subscribers = $subscribers->reject(function ($subscriber) use ($suppressedEmails) {
                return in_array($subscriber->email, $suppressedEmails);
            });
        }

        // Log subscriber status breakdown for debugging
        $totalSubscribers = $this->campaign->emailList->subscribers()->count();
        $confirmedCount = $this->campaign->emailList->subscribers()->where('status', 'confirmed')->count();
        $unconfirmedCount = $this->campaign->emailList->subscribers()->where('status', 'unconfirmed')->count();
        $unsubscribedCount = $this->campaign->emailList->subscribers()->where('status', 'unsubscribed')->count();
        
        Log::info("Campaign {$this->campaign->id} subscriber breakdown", [
            'total' => $totalSubscribers,
            'confirmed' => $confirmedCount,
            'unconfirmed' => $unconfirmedCount,
            'unsubscribed' => $unsubscribedCount,
        ]);

        if ($subscribers->isEmpty()) {
            throw new \RuntimeException(
                "Email list {$this->campaign->emailList->id} has no confirmed subscribers. " .
                "Total: {$totalSubscribers}, Confirmed: {$confirmedCount}, Unconfirmed: {$unconfirmedCount}, Unsubscribed: {$unsubscribedCount}. " .
                "Please confirm subscribers before starting the campaign."
            );
        }

        // Create recipient records in batches to handle large lists
        // Dynamic batch size based on subscriber count
        $subscriberCount = $subscribers->count();
        $batchSize = $subscriberCount > 25000 ? 250 : 500; // Smaller batches for very large lists
        $totalRecipients = 0;
        $processedBatches = 0;
        
        // Convert subscribers collection to array for chunking
        $subscriberArray = $subscribers->all();
        $subscriberChunks = array_chunk($subscriberArray, $batchSize);
        
        Log::info("Campaign {$this->campaign->id} processing " . count($subscriberChunks) . " batches of {$batchSize} recipients each (total: {$subscriberCount})");
        
        foreach ($subscriberChunks as $batchIndex => $batch) {
            $recipients = [];
            
            foreach ($batch as $subscriber) {
                $recipients[] = [
                    'campaign_id' => $this->campaign->id,
                    'email' => $subscriber->email,
                    'uuid' => (string) Str::uuid(),
                    'first_name' => $subscriber->first_name,
                    'last_name' => $subscriber->last_name,
                    'status' => 'pending',
                    'meta' => json_encode([
                        'custom_fields' => is_array($subscriber->custom_fields) ? $subscriber->custom_fields : [],
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Batch insert for this chunk
            try {
                CampaignRecipient::insert($recipients);
                $totalRecipients += count($recipients);
                $processedBatches++;
                
                // Log progress for large campaigns (more frequent for very large lists)
                $logFrequency = $subscriberCount > 25000 ? 5 : 10;
                if ($processedBatches % $logFrequency == 0 || count($subscriberChunks) <= $logFrequency) {
                    $progress = round(($processedBatches / count($subscriberChunks)) * 100, 1);
                    Log::info("Campaign {$this->campaign->id} processed batch " . ($batchIndex + 1) . "/" . count($subscriberChunks) . " (" . count($recipients) . " recipients) - {$progress}% complete");
                }
                
                // Progressive delay for very large campaigns to prevent database overload
                if (count($subscriberChunks) > 50) {
                    usleep(200000); // 0.2 second delay for very large campaigns
                } elseif (count($subscriberChunks) > 20) {
                    usleep(100000); // 0.1 second delay for large campaigns
                }
                
                // Memory cleanup for very large campaigns
                if ($processedBatches % 20 == 0 && $subscriberCount > 25000) {
                    gc_collect_cycles();
                    Log::debug("Campaign {$this->campaign->id} performed garbage collection at batch {$processedBatches}");
                }
                
            } catch (\Exception $e) {
                Log::error("Failed to insert batch " . ($batchIndex + 1) . " for campaign {$this->campaign->id}: " . $e->getMessage());
                throw new \RuntimeException("Failed to create recipients for campaign {$this->campaign->id}: " . $e->getMessage());
            }
        }

        // Update campaign total recipients count
        $this->campaign->update([
            'total_recipients' => $totalRecipients,
        ]);

        Log::info("Prepared {$totalRecipients} recipients for campaign {$this->campaign->id} in {$processedBatches} batches");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $errorMessage = $exception->getMessage();
        Log::error("StartCampaignJob failed for campaign {$this->campaign->id}: " . $errorMessage, [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString()
        ]);
        
        $this->campaign->update([
            'status' => 'failed',
            'finished_at' => now(),
            'failure_reason' => $errorMessage,
        ]);
    }
}

