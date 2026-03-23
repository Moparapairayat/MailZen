<?php

namespace App\Console\Commands;

use App\Models\ReplyServer;
use App\Services\ReplyProcessorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessReplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:process-replies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process campaign reply emails from the configured reply tracking inbox';

    /**
     * Execute the console command.
     */
    public function handle(ReplyProcessorService $replyProcessor): int
    {
        $this->info('Starting reply processing...');

        try {
            $processed = 0;
            $errors = 0;
            $startTime = now();

            // Process global IMAP settings if configured
            $imap = (array) config('mailzen.reply_tracking.imap', []);
            $hostname = trim((string) ($imap['hostname'] ?? ''));
            $username = trim((string) ($imap['username'] ?? ''));
            $password = trim((string) ($imap['password'] ?? ''));

            if ($hostname !== '' && $username !== '' && $password !== '') {
                try {
                    $globalProcessed = $replyProcessor->processReplies();
                    $processed += $globalProcessed;
                    $this->info("Global IMAP processed {$globalProcessed} repl(ies).");
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Global IMAP processing failed: " . $e->getMessage());
                    Log::error('Global IMAP processing failed', ['error' => $e->getMessage()]);
                }
            }

            // Get all active reply servers with load balancing
            $servers = ReplyServer::query()
                ->where('active', true)
                ->orderBy('id')
                ->get();

            if ($servers->isEmpty()) {
                $this->info('No active reply servers found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$servers->count()} active reply server(s).");

            // Implement load balancing: process servers in round-robin based on last processing time
            $servers = $servers->sortBy(function ($server) {
                return $server->last_processed_at?->timestamp ?? 0;
            });

            foreach ($servers as $index => $server) {
                try {
                    $this->info("Processing reply server #{$server->id} ({$server->name})...");
                    
                    $serverProcessed = $replyProcessor->processReplies($server);
                    $processed += $serverProcessed;
                    
                    $this->info("Reply server #{$server->id} processed {$serverProcessed} repl(ies).");
                    
                    // Add delay between servers to prevent overwhelming mail servers
                    if ($index < $servers->count() - 1) {
                        $delay = config('mailzen.reply_tracking.server_delay', 2000); // 2 seconds default
                        usleep($delay * 1000); // Convert to microseconds
                    }
                    
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("Reply server #{$server->id} processing failed: " . $e->getMessage());
                    Log::error('Reply server processing failed', [
                        'server_id' => $server->id,
                        'server_name' => $server->name,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Continue with next server instead of failing completely
                    continue;
                }
            }

            $totalTime = now()->diffInSeconds($startTime);
            
            $this->info("Reply processing completed:");
            $this->info("  Total processed: {$processed} repl(ies)");
            $this->info("  Errors: {$errors}");
            $this->info("  Total time: {$totalTime} seconds");
            $this->info("  Servers processed: {$servers->count()}");
            
            Log::info('Reply processing summary', [
                'total_processed' => $processed,
                'total_errors' => $errors,
                'total_time_seconds' => $totalTime,
                'servers_processed' => $servers->count(),
                'processed_at' => now()->toIso8601String(),
            ]);
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Critical error in reply processing: ' . $e->getMessage());
            Log::error('Critical error in reply processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }
    }
}
