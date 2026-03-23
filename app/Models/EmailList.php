<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailList extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'sending_domain_id',
        'name',
        'display_name',
        'description',
        'from_name',
        'from_email',
        'reply_to',
        'status',
        'opt_in',
        'opt_out',
        'double_opt_in',
        'default_subject',
        'company_name',
        'company_address',
        'footer_text',
        'welcome_email_enabled',
        'welcome_email_subject',
        'welcome_email_content',
        'unsubscribe_email_enabled',
        'unsubscribe_email_subject',
        'unsubscribe_email_content',
        'unsubscribe_redirect_url',
        'gdpr_enabled',
        'gdpr_text',
        'custom_fields',
        'tags',
        'subscribers_count',
        'confirmed_subscribers_count',
        'unsubscribed_count',
        'bounced_count',
        'last_subscriber_at',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'tags' => 'array',
            'double_opt_in' => 'boolean',
            'welcome_email_enabled' => 'boolean',
            'unsubscribe_email_enabled' => 'boolean',
            'gdpr_enabled' => 'boolean',
            'last_subscriber_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function sendingDomain(): BelongsTo
    {
        return $this->belongsTo(SendingDomain::class, 'sending_domain_id');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(ListSubscriber::class, 'list_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'list_id');
    }

    public function autoResponders(): HasMany
    {
        return $this->hasMany(AutoResponder::class, 'list_id');
    }

    public function bounceLogs(): HasMany
    {
        return $this->hasMany(BounceLog::class, 'subscriber_id')
            ->whereHas('subscriber', function ($q) {
                $q->where('list_id', $this->id);
            });
    }

    public function confirmedSubscribers(): HasMany
    {
        return $this->hasMany(ListSubscriber::class, 'list_id')
            ->where('status', 'confirmed');
    }

    public function segments(): HasMany
    {
        return $this->hasMany(ListSegment::class, 'list_id');
    }

    public function subscriptionForms(): HasMany
    {
        return $this->hasMany(SubscriptionForm::class, 'list_id');
    }
}
