<?php

namespace App\Console\Commands;

use App\Models\BouncedEmail;
use App\Models\BounceLog;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\ListSubscriber;
use Illuminate\Console\Command;

class BouncesBackfillAttribution extends Command
{
    protected $signature = 'bounces:backfill-attribution
                            {--dry-run : Do not write changes}
                            {--limit=500 : Max records to scan per table}';

    protected $description = 'Backfill campaign/list/recipient attribution for bounce records when headers were stripped (extract recipient UUID from raw_message)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('Backfilling bounced_emails...');
        $updatedBouncedEmails = $this->backfillBouncedEmails($dryRun, $limit);

        $this->info('Backfilling bounce_logs...');
        $updatedBounceLogs = $this->backfillBounceLogs($dryRun, $limit);

        $this->info("Done. Updated bounced_emails={$updatedBouncedEmails}, bounce_logs={$updatedBounceLogs}" . ($dryRun ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }

    protected function backfillBouncedEmails(bool $dryRun, int $limit): int
    {
        $rows = BouncedEmail::query()
            ->where(function ($q) {
                $q->whereNull('recipient_id')
                    ->orWhereNull('campaign_id')
                    ->orWhereNull('list_id');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $updated = 0;

        foreach ($rows as $row) {
            $uuid = $this->extractRecipientUuidFromRawMessage((string) $row->raw_message);
            if (!$uuid) {
                continue;
            }

            $recipient = CampaignRecipient::where('uuid', $uuid)->first();
            if (!$recipient) {
                continue;
            }

            $campaignId = $recipient->campaign_id;
            $listId = Campaign::where('id', $campaignId)->value('list_id');

            $subscriberId = $row->subscriber_id;
            if (!$subscriberId && $listId) {
                $subscriberId = ListSubscriber::where('email', $row->email)
                    ->where('list_id', $listId)
                    ->value('id');
            }

            $changes = [
                'recipient_id' => $row->recipient_id ?? $recipient->id,
                'campaign_id' => $row->campaign_id ?? $campaignId,
                'list_id' => $row->list_id ?? $listId,
                'subscriber_id' => $row->subscriber_id ?? $subscriberId,
            ];

            $dirty = false;
            foreach ($changes as $k => $v) {
                if ($v !== null && $row->{$k} !== $v) {
                    $dirty = true;
                    break;
                }
            }

            if (!$dirty) {
                continue;
            }

            $updated++;

            if ($dryRun) {
                $this->line("[bounced_emails#{$row->id}] {$row->email} -> campaign_id={$changes['campaign_id']} list_id={$changes['list_id']} recipient_id={$changes['recipient_id']}");
                continue;
            }

            $row->update($changes);
        }

        return $updated;
    }

    protected function backfillBounceLogs(bool $dryRun, int $limit): int
    {
        $rows = BounceLog::query()
            ->where(function ($q) {
                $q->whereNull('recipient_id')
                    ->orWhereNull('campaign_id')
                    ->orWhereNull('list_id');
            })
            ->orderBy('id')
            ->limit($limit)
            ->get();

        $updated = 0;

        foreach ($rows as $row) {
            $uuid = $this->extractRecipientUuidFromRawMessage((string) $row->raw_message);
            if (!$uuid) {
                continue;
            }

            $recipient = CampaignRecipient::where('uuid', $uuid)->first();
            if (!$recipient) {
                continue;
            }

            $campaignId = $recipient->campaign_id;
            $listId = Campaign::where('id', $campaignId)->value('list_id');

            $subscriberId = $row->subscriber_id;
            if (!$subscriberId && $listId) {
                $subscriberId = ListSubscriber::where('email', $row->email)
                    ->where('list_id', $listId)
                    ->value('id');
            }

            $changes = [
                'recipient_id' => $row->recipient_id ?? $recipient->id,
                'campaign_id' => $row->campaign_id ?? $campaignId,
                'list_id' => $row->list_id ?? $listId,
                'subscriber_id' => $row->subscriber_id ?? $subscriberId,
            ];

            $dirty = false;
            foreach ($changes as $k => $v) {
                if ($v !== null && $row->{$k} !== $v) {
                    $dirty = true;
                    break;
                }
            }

            if (!$dirty) {
                continue;
            }

            $updated++;

            if ($dryRun) {
                $this->line("[bounce_logs#{$row->id}] {$row->email} -> campaign_id={$changes['campaign_id']} list_id={$changes['list_id']} recipient_id={$changes['recipient_id']}");
                continue;
            }

            $row->update($changes);
        }

        return $updated;
    }

    protected function extractRecipientUuidFromRawMessage(string $rawMessage): ?string
    {
        $patterns = [
            '/\/unsubscribe\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/\/track\/open\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/\/track\/click\/([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})/i',
            '/\b([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})\b/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $rawMessage, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }
}
