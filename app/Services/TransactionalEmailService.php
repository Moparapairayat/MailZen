<?php

namespace App\Services;

use App\Models\TransactionalEmail;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TransactionalEmailService
{
    /**
     * Get paginated list of transactional emails for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TransactionalEmail::where('customer_id', $customer->id);

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('key', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new transactional email.
     */
    public function create(Customer $customer, array $data): TransactionalEmail
    {
        return TransactionalEmail::create([
            'customer_id' => $customer->id,
            'name' => $data['name'],
            'key' => $data['key'] ?? Str::slug($data['name']),
            'subject' => $data['subject'],
            'from_name' => $data['from_name'] ?? null,
            'from_email' => $data['from_email'] ?? $customer->email,
            'reply_to' => $data['reply_to'] ?? null,
            'html_content' => $data['html_content'] ?? null,
            'plain_text_content' => $data['plain_text_content'] ?? null,
            'template_variables' => $data['template_variables'] ?? [],
            'status' => $data['status'] ?? 'active',
            'description' => $data['description'] ?? null,
            'track_opens' => $data['track_opens'] ?? false,
            'track_clicks' => $data['track_clicks'] ?? false,
        ]);
    }

    /**
     * Update an existing transactional email.
     */
    public function update(TransactionalEmail $transactionalEmail, array $data): TransactionalEmail
    {
        $transactionalEmail->update($data);
        return $transactionalEmail->fresh();
    }

    /**
     * Delete a transactional email.
     */
    public function delete(TransactionalEmail $transactionalEmail): bool
    {
        return $transactionalEmail->delete();
    }

    /**
     * Send a transactional email.
     */
    public function send(TransactionalEmail $transactionalEmail, string $to, array $variables = []): bool
    {
        // In a real implementation, this would send the email
        // For now, just increment the sent count
        $transactionalEmail->increment('sent_count');
        
        return true;
    }
}

