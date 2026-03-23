<?php

namespace App\Console\Commands;

use App\Services\SubscriberImportProcessor;
use Illuminate\Console\Command;

class ProcessSubscriberImports extends Command
{
    protected $signature = 'subscriber-imports:process {--limit=3} {--rows=2000} {--max-seconds=45}';

    protected $description = 'Process queued/running subscriber CSV imports in small chunks (cron-friendly)';

    public function handle(): int
    {
        $limit = (int) ($this->option('limit') ?? 3);
        $rows = (int) ($this->option('rows') ?? 2000);
        $maxSeconds = (int) ($this->option('max-seconds') ?? 45);

        $processor = app(SubscriberImportProcessor::class);
        $processed = $processor->processDueImports($limit, $rows, $maxSeconds);

        if ($processed > 0) {
            $this->info("Processed {$processed} import(s).");
        }

        return Command::SUCCESS;
    }
}
