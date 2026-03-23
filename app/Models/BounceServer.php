<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class BounceServer extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($bounceServer) {
            // Auto-disable SSL for known problematic hosting providers
            $problematicHosts = [
                'siteground.eu',
                'siteground.net', 
                'siteground.com', 
                'hostinger.com',
                'hostinger.co.uk',
                'bluehost.com',
                'bluehost.in',
                'godaddy.com',
                'namecheap.com',
                'uk1006.siteground.eu'
            ];
            
            foreach ($problematicHosts as $host) {
                if (str_contains(strtolower($bounceServer->hostname ?? ''), $host)) {
                    $bounceServer->validate_ssl = false;
                    Log::info('Auto-disabled SSL validation for known problematic host', [
                        'hostname' => $bounceServer->hostname,
                        'host_pattern' => $host
                    ]);
                    break;
                }
            }
        });
    }

    protected $fillable = [
        'customer_id',
        'name',
        'protocol',
        'hostname',
        'port',
        'encryption',
        'username',
        'password',
        'mailbox',
        'active',
        'delete_after_processing',
        'max_emails_per_batch',
        'validate_ssl',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'delete_after_processing' => 'boolean',
            'validate_ssl' => 'boolean',
            'port' => 'integer',
            'max_emails_per_batch' => 'integer',
        ];
    }

    /**
     * Encrypt password when setting.
     */
    public function setPasswordAttribute($value): void
    {
        $value = (string) ($value ?? '');
        $value = rtrim($value);
        $this->attributes['password'] = $value;
    }

    /**
     * Decrypt password when getting.
     */
    public function getPasswordAttribute($value): string
    {
        return (string) ($value ?? '');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function bounceLogs(): HasMany
    {
        return $this->hasMany(BounceLog::class);
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
