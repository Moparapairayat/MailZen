<?php

namespace App\Console\Commands;

use App\Jobs\StartCampaignJob;
use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class StartScheduledCampaigns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:start-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start campaigns that are scheduled to run now';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for scheduled campaigns...');

        // Find campaigns scheduled to start now (or earlier) that have not started yet
        $scheduledCampaigns = Campaign::where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->whereNull('started_at')
            ->get();

        if ($scheduledCampaigns->isEmpty()) {
            $this->info('No campaigns scheduled to start.');
            return Command::SUCCESS;
        }

        $this->info("Found {$scheduledCampaigns->count()} campaign(s) to start.");

        foreach ($scheduledCampaigns as $campaign) {
            try {
                // Double-check campaign can still be started
                if (!$campaign->canStart()) {
                    $this->warn("Campaign {$campaign->id} ({$campaign->name}) cannot be started. Status: {$campaign->status}");
                    continue;
                }

                // Validate campaign readiness
                if (!$campaign->list_id) {
                    $this->error("Campaign {$campaign->id} ({$campaign->name}) has no email list selected.");
                    $campaign->update(['status' => 'failed']);
                    continue;
                }

                if (!$campaign->html_content && !$campaign->plain_text_content) {
                    $this->error("Campaign {$campaign->id} ({$campaign->name}) has no content.");
                    $campaign->update(['status' => 'failed']);
                    continue;
                }

                // Dispatch start job
                StartCampaignJob::dispatch($campaign)->onQueue('campaigns');

                $this->info("Dispatched campaign {$campaign->id} ({$campaign->name}) to start.");
                Log::info("Scheduled campaign {$campaign->id} ({$campaign->name}) started via scheduler.");

            } catch (\Exception $e) {
                $this->error("Failed to start campaign {$campaign->id}: " . $e->getMessage());
                Log::error("Failed to start scheduled campaign {$campaign->id}: " . $e->getMessage());
                
                $campaign->update([
                    'status' => 'failed',
                ]);
            }
        }

        return Command::SUCCESS;
    }
}

