<?php

namespace App\Models;

use App\Services\BounceProcessingService;
use App\Services\CampaignAnalyticsService;
use App\Services\CampaignSendingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'list_id',
        'template_id',
        'name',
        'subject',
        'from_name',
        'from_email',
        'reply_to',
        'type',
        'status',
        'html_content',
        'plain_text_content',
        'template_data',
        'scheduled_at',
        'send_at',
        'started_at',
        'finished_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'opened_count',
        'clicked_count',
        'failed_count',
        'failure_reason',
        'bounced_count',
        'unsubscribed_count',
        'complained_count',
        'replied_count',
        'open_rate',
        'click_rate',
        'bounce_rate',
        'track_opens',
        'track_clicks',
        'tracking_domain_id',
        'bounce_server_id',
        'sending_domain_id',
        'delivery_server_id',
        'reply_server_id',
        'segments',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'template_data' => 'array',
            'segments' => 'array',
            'settings' => 'array',
            'scheduled_at' => 'datetime',
            'send_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'open_rate' => 'decimal:2',
            'click_rate' => 'decimal:2',
            'bounce_rate' => 'decimal:2',
            'track_opens' => 'boolean',
            'track_clicks' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class, 'list_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function trackingDomain(): BelongsTo
    {
        return $this->belongsTo(TrackingDomain::class, 'tracking_domain_id');
    }

    public function bounceServer(): BelongsTo
    {
        return $this->belongsTo(BounceServer::class, 'bounce_server_id');
    }

    public function deliveryServer(): BelongsTo
    {
        return $this->belongsTo(DeliveryServer::class, 'delivery_server_id');
    }

    public function replyServer(): BelongsTo
    {
        return $this->belongsTo(ReplyServer::class, 'reply_server_id');
    }

    public function sendingDomain(): BelongsTo
    {
        return $this->belongsTo(SendingDomain::class, 'sending_domain_id');
    }

    public function bounceLogs(): HasMany
    {
        return $this->hasMany(BounceLog::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(CampaignTracking::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(CampaignVariant::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(CampaignRecipient::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CampaignReply::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    public function hasAbTest(): bool
    {
        return $this->variants()->count() > 0;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    public function isQueued(): bool
    {
        return $this->status === 'queued';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function canStart(): bool
    {
        return in_array($this->status, ['draft', 'scheduled']);
    }

    public function canPause(): bool
    {
        return $this->status === 'running';
    }

    public function canResume(): bool
    {
        return $this->status === 'paused';
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
    }

    public function incrementOpenedCount(): void
    {
        $this->increment('opened_count');
        $this->updateRates();
    }

    public function incrementClickedCount(): void
    {
        $this->increment('clicked_count');
        $this->updateRates();
    }

    public function incrementFailedCount(): void
    {
        $this->increment('failed_count');
    }

    public function incrementBouncedCount(): void
    {
        $this->increment('bounced_count');
        $this->updateRates();
    }

    public function incrementRepliedCount(): void
    {
        $this->increment('replied_count');
    }

    protected function updateRates(): void
    {
        // Get unique opens from recipient statuses for accurate rate calculation
        $recipientStats = $this->recipients()
            ->selectRaw('
                SUM(CASE WHEN status = "opened" OR status = "clicked" THEN 1 ELSE 0 END) as unique_opens,
                SUM(CASE WHEN status = "clicked" THEN 1 ELSE 0 END) as unique_clicks,
                SUM(CASE WHEN status = "bounced" THEN 1 ELSE 0 END) as bounced
            ')
            ->first();
        
        $delivered = max(0, $this->sent_count - ($recipientStats->bounced ?? 0));
        
        if ($delivered > 0) {
            $this->open_rate = round((($recipientStats->unique_opens ?? 0) / $delivered) * 100, 2);
            $this->click_rate = round((($recipientStats->unique_clicks ?? 0) / $delivered) * 100, 2);
            $this->bounce_rate = round(($this->bounced_count / $this->sent_count) * 100, 2);
            $this->saveQuietly();
        }
    }

    public function checkCompletion(): void
    {
        $pendingCount = $this->recipients()
            ->where('status', 'pending')
            ->count();

        if ($pendingCount === 0 && $this->isRunning()) {
            $this->update([
                'status' => 'completed',
                'finished_at' => now(),
            ]);
        }
    }

    /**
     * Sync campaign statistics from recipient statuses.
     * This ensures sent_count, bounced_count, etc. match actual recipient statuses.
     */
    public function syncStats(): void
    {
        $stats = $this->recipients()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "sent" OR status = "opened" OR status = "clicked" THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN status = "opened" OR status = "clicked" THEN 1 ELSE 0 END) as opened,
                SUM(CASE WHEN status = "clicked" THEN 1 ELSE 0 END) as clicked,
                SUM(CASE WHEN replied_at IS NOT NULL THEN 1 ELSE 0 END) as replied,
                SUM(CASE WHEN status = "bounced" THEN 1 ELSE 0 END) as bounced,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        $this->update([
            'sent_count' => $stats->sent ?? 0,
            'opened_count' => $stats->opened ?? 0,
            'clicked_count' => $stats->clicked ?? 0,
            'replied_count' => $stats->replied ?? 0,
            'bounced_count' => $stats->bounced ?? 0,
            'failed_count' => $stats->failed ?? 0,
            'total_recipients' => $stats->total ?? 0,
        ]);

        $this->updateRates();
    }
}
