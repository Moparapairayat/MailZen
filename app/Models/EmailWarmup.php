<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailWarmup extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'delivery_server_id',
        'email_list_id',
        'name',
        'from_email',
        'from_name',
        'status',
        'starting_volume',
        'max_volume',
        'daily_increase_rate',
        'current_day',
        'total_days',
        'send_time',
        'timezone',
        'total_sent',
        'total_opened',
        'total_clicked',
        'total_bounced',
        'total_complained',
        'email_templates',
        'settings',
        'started_at',
        'completed_at',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'email_templates' => 'array',
            'settings' => 'array',
            'daily_increase_rate' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryServer(): BelongsTo
    {
        return $this->belongsTo(DeliveryServer::class);
    }

    public function emailList(): BelongsTo
    {
        return $this->belongsTo(EmailList::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WarmupLog::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(WarmupEmail::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canStart(): bool
    {
        return in_array($this->status, ['draft', 'paused']);
    }

    public function canPause(): bool
    {
        return $this->status === 'active';
    }

    public function calculateVolumeForDay(int $day): int
    {
        if ($day <= 0) {
            return $this->starting_volume;
        }

        $volume = $this->starting_volume * pow($this->daily_increase_rate, $day - 1);
        
        return min((int) round($volume), $this->max_volume);
    }

    public function getTodayTargetVolume(): int
    {
        return $this->calculateVolumeForDay($this->current_day + 1);
    }

    public function getProgressPercentage(): float
    {
        if ($this->total_days <= 0) {
            return 0;
        }

        return min(100, round(($this->current_day / $this->total_days) * 100, 1));
    }

    public function getOpenRate(): float
    {
        if ($this->total_sent <= 0) {
            return 0;
        }

        return round(($this->total_opened / $this->total_sent) * 100, 2);
    }

    public function getClickRate(): float
    {
        if ($this->total_sent <= 0) {
            return 0;
        }

        return round(($this->total_clicked / $this->total_sent) * 100, 2);
    }

    public function getBounceRate(): float
    {
        if ($this->total_sent <= 0) {
            return 0;
        }

        return round(($this->total_bounced / $this->total_sent) * 100, 2);
    }

    public function getHealthScore(): string
    {
        $bounceRate = $this->getBounceRate();
        $openRate = $this->getOpenRate();

        if ($bounceRate > 5 || $this->total_complained > 0) {
            return 'poor';
        }

        if ($bounceRate > 2 || $openRate < 10) {
            return 'fair';
        }

        if ($openRate >= 20) {
            return 'excellent';
        }

        return 'good';
    }

    public function incrementStats(string $type, int $count = 1): void
    {
        $field = match ($type) {
            'sent' => 'total_sent',
            'opened' => 'total_opened',
            'clicked' => 'total_clicked',
            'bounced' => 'total_bounced',
            'complained' => 'total_complained',
            default => null,
        };

        if ($field) {
            $this->increment($field, $count);
        }
    }
}
