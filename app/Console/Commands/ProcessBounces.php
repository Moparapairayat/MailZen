<?php

namespace App\Console\Commands;

use App\Models\BounceServer;
use App\Services\BounceProcessorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessBounces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-bounces 
                            {--server= : Process specific bounce server by ID}
                            {--all : Process all active bounce servers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process bounce emails from configured bounce servers';

    /**
     * Execute the console command.
     */
    public function handle(BounceProcessorService $bounceProcessor): int
    {
        $this->info('Starting bounce processing...');

        $servers = $this->getBounceServers();

        if ($servers->isEmpty()) {
            $this->warn('No active bounce servers found.');
            return Command::FAILURE;
        }

        $totalProcessed = 0;

        foreach ($servers as $server) {
            try {
                $this->info("Processing bounce server: {$server->name} (ID: {$server->id})");
                
                $processed = $bounceProcessor->processBounces($server);
                $totalProcessed += $processed;

                $this->info("Processed {$processed} bounce(s) from {$server->name}");
            } catch (\Exception $e) {
                $this->error("Error processing bounce server {$server->name}: " . $e->getMessage());
                Log::error("Error processing bounce server {$server->id}: " . $e->getMessage());
            }
        }

        $this->info("Bounce processing completed. Total processed: {$totalProcessed}");

        return Command::SUCCESS;
    }

    /**
     * Get bounce servers to process.
     */
    protected function getBounceServers()
    {
        if ($serverId = $this->option('server')) {
            return BounceServer::where('id', $serverId)
                ->where('active', true)
                ->get();
        }

        return BounceServer::where('active', true)->get();
    }
}
