<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuppressionList extends Model
{
    use HasFactory;

    protected $table = 'suppression_list';

    protected $fillable = [
        'customer_id',
        'email',
        'reason',
        'reason_description',
        'subscriber_id',
        'campaign_id',
        'suppressed_at',
    ];

    protected function casts(): array
    {
        return [
            'suppressed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(ListSubscriber::class, 'subscriber_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Check if an email is suppressed.
     */
    public static function isSuppressed(string $email, ?int $customerId = null): bool
    {
        $query = static::where('email', $email);
        
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        
        return $query->exists();
    }
}
