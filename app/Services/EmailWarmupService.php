<?php

namespace App\Services;

use App\Models\EmailWarmup;
use App\Models\WarmupLog;
use App\Models\WarmupEmail;
use App\Models\ListSubscriber;
use App\Jobs\ProcessWarmupJob;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmailWarmupService
{
    protected array $defaultTemplates = [
        [
            'subject' => 'Quick question about your experience',
            'body' => "Hi there,\n\nI hope this email finds you well. I wanted to reach out and see how things are going.\n\nWould love to hear your thoughts when you have a moment.\n\nBest regards",
        ],
        [
            'subject' => 'Following up on our connection',
            'body' => "Hello,\n\nI hope you're having a great day! Just wanted to touch base and see if there's anything I can help you with.\n\nLooking forward to hearing from you.\n\nWarm regards",
        ],
        [
            'subject' => 'Checking in',
            'body' => "Hi,\n\nI wanted to drop you a quick note to see how everything is going on your end.\n\nFeel free to reach out if you need anything.\n\nBest",
        ],
        [
            'subject' => 'A quick hello',
            'body' => "Hello there,\n\nJust sending a quick hello and hoping all is well with you.\n\nLet me know if there's anything I can assist with.\n\nCheers",
        ],
        [
            'subject' => 'Touching base',
            'body' => "Hi,\n\nI hope this message finds you in good spirits. I wanted to reach out and reconnect.\n\nWould be great to catch up when you have time.\n\nAll the best",
        ],
    ];

    public function getPaginated(int $customerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmailWarmup::where('customer_id', $customerId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->with('deliveryServer')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): EmailWarmup
    {
        $data['email_templates'] = $data['email_templates'] ?? $this->defaultTemplates;
        $data['settings'] = $data['settings'] ?? [
            'reply_tracking' => true,
            'auto_pause_on_high_bounce' => true,
            'bounce_threshold' => 5,
        ];

        return EmailWarmup::create($data);
    }

    public function update(EmailWarmup $warmup, array $data): EmailWarmup
    {
        $warmup->update($data);
        return $warmup->fresh();
    }

    public function delete(EmailWarmup $warmup): bool
    {
        return $warmup->delete();
    }

    public function start(EmailWarmup $warmup): EmailWarmup
    {
        if (!$warmup->canStart()) {
            throw new \Exception('Warmup cannot be started in its current state.');
        }

        $warmup->update([
            'status' => 'active',
            'started_at' => $warmup->started_at ?? now(),
        ]);

        $this->scheduleNextSend($warmup);

        Log::info('Email warmup started', [
            'warmup_id' => $warmup->id,
            'customer_id' => $warmup->customer_id,
        ]);

        return $warmup->fresh();
    }

    public function pause(EmailWarmup $warmup): EmailWarmup
    {
        if (!$warmup->canPause()) {
            throw new \Exception('Warmup cannot be paused in its current state.');
        }

        $warmup->update(['status' => 'paused']);

        Log::info('Email warmup paused', [
            'warmup_id' => $warmup->id,
        ]);

        return $warmup->fresh();
    }

    public function complete(EmailWarmup $warmup): EmailWarmup
    {
        $warmup->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info('Email warmup completed', [
            'warmup_id' => $warmup->id,
            'total_sent' => $warmup->total_sent,
        ]);

        return $warmup->fresh();
    }

    public function scheduleNextSend(EmailWarmup $warmup): void
    {
        if (!$warmup->isActive()) {
            return;
        }

        $nextDay = $warmup->current_day + 1;

        if ($nextDay > $warmup->total_days) {
            $this->complete($warmup);
            return;
        }

        $sendTime = Carbon::parse($warmup->send_time, $warmup->timezone);
        $now = Carbon::now($warmup->timezone);

        if ($now->gt($sendTime)) {
            $sendTime = $sendTime->addDay();
        }

        $delay = $now->diffInSeconds($sendTime);

        ProcessWarmupJob::dispatch($warmup->id)
            ->delay(now()->addSeconds($delay));

        Log::info('Warmup job scheduled', [
            'warmup_id' => $warmup->id,
            'next_day' => $nextDay,
            'scheduled_for' => $sendTime->toDateTimeString(),
        ]);
    }

    public function processDay(EmailWarmup $warmup): WarmupLog
    {
        if (!$warmup->isActive()) {
            throw new \Exception('Warmup is not active.');
        }

        $nextDay = $warmup->current_day + 1;
        $targetVolume = $warmup->calculateVolumeForDay($nextDay);
        $today = now()->toDateString();

        $existingLog = WarmupLog::where('email_warmup_id', $warmup->id)
            ->where('send_date', $today)
            ->first();

        if ($existingLog && $existingLog->isCompleted()) {
            Log::info('Warmup day already processed', [
                'warmup_id' => $warmup->id,
                'day' => $nextDay,
            ]);
            return $existingLog;
        }

        $log = $existingLog ?? WarmupLog::create([
            'email_warmup_id' => $warmup->id,
            'send_date' => $today,
            'day_number' => $nextDay,
            'target_volume' => $targetVolume,
            'status' => 'pending',
        ]);

        $log->markAsStarted();

        try {
            $sentCount = $this->sendWarmupEmails($warmup, $log, $targetVolume);

            $warmup->update([
                'current_day' => $nextDay,
                'last_sent_at' => now(),
            ]);

            $log->markAsCompleted();

            if ($nextDay >= $warmup->total_days) {
                $this->complete($warmup);
            } else {
                $this->scheduleNextSend($warmup);
            }

            Log::info('Warmup day processed successfully', [
                'warmup_id' => $warmup->id,
                'day' => $nextDay,
                'sent' => $sentCount,
                'target' => $targetVolume,
            ]);

        } catch (\Exception $e) {
            $log->markAsFailed($e->getMessage());

            Log::error('Warmup day processing failed', [
                'warmup_id' => $warmup->id,
                'day' => $nextDay,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        return $log;
    }

    protected function sendWarmupEmails(EmailWarmup $warmup, WarmupLog $log, int $targetVolume): int
    {
        $warmup->loadMissing('deliveryServer', 'emailList');

        if (!$warmup->deliveryServer) {
            throw new \Exception('No delivery server configured for warmup.');
        }

        $recipients = $this->getRecipients($warmup, $targetVolume);

        if (empty($recipients)) {
            throw new \Exception('No recipients available for warmup emails.');
        }

        $deliveryServerService = app(DeliveryServerService::class);
        $deliveryServerService->configureMailFromServer($warmup->deliveryServer);

        $templates = $warmup->email_templates ?? $this->defaultTemplates;
        $sentCount = 0;

        foreach ($recipients as $recipient) {
            try {
                $template = $templates[array_rand($templates)];

                $warmupEmail = WarmupEmail::create([
                    'warmup_log_id' => $log->id,
                    'email_warmup_id' => $warmup->id,
                    'email' => $recipient,
                    'subject' => $template['subject'],
                    'status' => 'pending',
                ]);

                $this->sendSingleEmail($warmup, $warmupEmail, $template);

                $sentCount++;
                $log->incrementStat('sent');
                $warmup->incrementStats('sent');

                usleep(500000);

            } catch (\Exception $e) {
                Log::warning('Failed to send warmup email', [
                    'warmup_id' => $warmup->id,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sentCount;
    }

    protected function sendSingleEmail(EmailWarmup $warmup, WarmupEmail $warmupEmail, array $template): void
    {
        $fromEmail = $warmup->from_email;
        $fromName = $warmup->from_name ?? config('mail.from.name');

        $htmlBody = '<div style="font-family: Arial, sans-serif; line-height: 1.6;">'
            . nl2br(htmlspecialchars($template['body'], ENT_QUOTES, 'UTF-8'))
            . '</div>';

        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($warmupEmail, $template, $fromEmail, $fromName, $htmlBody) {
            $message->to($warmupEmail->email)
                ->subject($template['subject'])
                ->from($fromEmail, $fromName)
                ->html($htmlBody);
        });

        $warmupEmail->markAsSent();
    }

    protected function getRecipients(EmailWarmup $warmup, int $count): array
    {
        if ($warmup->emailList) {
            return ListSubscriber::where('list_id', $warmup->email_list_id)
                ->where('status', 'confirmed')
                ->inRandomOrder()
                ->limit($count)
                ->pluck('email')
                ->toArray();
        }

        $settings = $warmup->settings ?? [];
        $seedEmails = $settings['seed_emails'] ?? [];

        if (!empty($seedEmails)) {
            $result = [];
            for ($i = 0; $i < $count; $i++) {
                $result[] = $seedEmails[$i % count($seedEmails)];
            }
            return $result;
        }

        return [$warmup->from_email];
    }

    public function getStats(EmailWarmup $warmup): array
    {
        $logs = $warmup->logs()
            ->orderBy('day_number')
            ->get();

        $dailyData = $logs->map(function ($log) {
            return [
                'day' => $log->day_number,
                'date' => $log->send_date->format('M d'),
                'sent' => $log->sent_count,
                'target' => $log->target_volume,
                'opened' => $log->opened_count,
                'clicked' => $log->clicked_count,
                'bounced' => $log->bounced_count,
                'open_rate' => $log->open_rate ?? 0,
                'bounce_rate' => $log->bounce_rate ?? 0,
            ];
        });

        return [
            'total_sent' => $warmup->total_sent,
            'total_opened' => $warmup->total_opened,
            'total_clicked' => $warmup->total_clicked,
            'total_bounced' => $warmup->total_bounced,
            'open_rate' => $warmup->getOpenRate(),
            'click_rate' => $warmup->getClickRate(),
            'bounce_rate' => $warmup->getBounceRate(),
            'health_score' => $warmup->getHealthScore(),
            'progress' => $warmup->getProgressPercentage(),
            'current_day' => $warmup->current_day,
            'total_days' => $warmup->total_days,
            'daily_data' => $dailyData,
        ];
    }

    public function checkAndPauseIfNeeded(EmailWarmup $warmup): bool
    {
        $settings = $warmup->settings ?? [];
        $autoPause = $settings['auto_pause_on_high_bounce'] ?? true;
        $threshold = $settings['bounce_threshold'] ?? 5;

        if (!$autoPause) {
            return false;
        }

        if ($warmup->getBounceRate() > $threshold) {
            $this->pause($warmup);

            Log::warning('Warmup auto-paused due to high bounce rate', [
                'warmup_id' => $warmup->id,
                'bounce_rate' => $warmup->getBounceRate(),
                'threshold' => $threshold,
            ]);

            return true;
        }

        return false;
    }

    public function getDefaultTemplates(): array
    {
        return $this->defaultTemplates;
    }
}
