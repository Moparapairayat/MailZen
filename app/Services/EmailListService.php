<?php

namespace App\Services;

use App\Models\EmailList;
use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

class EmailListService
{
    /**
     * Get paginated list of email lists for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmailList::where('customer_id', $customer->id)
            ->withCount(['subscribers', 'confirmedSubscribers']);

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new email list.
     */
    public function create(Customer $customer, array $data): EmailList
    {
        return EmailList::create([
            'customer_id' => $customer->id,
            'name' => $data['name'],
            'display_name' => $data['display_name'] ?? $data['name'],
            'description' => $data['description'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'from_email' => $data['from_email'] ?? $customer->email,
            'reply_to' => $data['reply_to'] ?? null,
            'status' => $data['status'] ?? 'active',
            'opt_in' => $data['opt_in'] ?? 'double',
            'opt_out' => $data['opt_out'] ?? 'single',
            'double_opt_in' => $data['double_opt_in'] ?? true,
            'default_subject' => $data['default_subject'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'footer_text' => $data['footer_text'] ?? null,
            'welcome_email_enabled' => $data['welcome_email_enabled'] ?? true,
            'welcome_email_subject' => $data['welcome_email_subject'] ?? null,
            'welcome_email_content' => $data['welcome_email_content'] ?? null,
            'unsubscribe_email_enabled' => $data['unsubscribe_email_enabled'] ?? true,
            'unsubscribe_email_subject' => $data['unsubscribe_email_subject'] ?? null,
            'unsubscribe_email_content' => $data['unsubscribe_email_content'] ?? null,
            'unsubscribe_redirect_url' => $data['unsubscribe_redirect_url'] ?? null,
            'gdpr_enabled' => $data['gdpr_enabled'] ?? false,
            'gdpr_text' => $data['gdpr_text'] ?? null,
            'custom_fields' => $data['custom_fields'] ?? [],
            'tags' => $data['tags'] ?? [],
        ]);
    }

    /**
     * Update an existing email list.
     */
    public function update(EmailList $emailList, array $data): EmailList
    {
        $emailList->update($data);
        return $emailList->fresh();
    }

    /**
     * Delete an email list.
     */
    public function delete(EmailList $emailList): bool
    {
        return $emailList->delete();
    }

    /**
     * Update subscriber counts for a list.
     */
    public function updateSubscriberCounts(EmailList $emailList): void
    {
        $emailList->update([
            'subscribers_count' => $emailList->subscribers()->count(),
            'confirmed_subscribers_count' => $emailList->subscribers()->where('status', 'confirmed')->count(),
            'unsubscribed_count' => $emailList->subscribers()->where('status', 'unsubscribed')->count(),
            'bounced_count' => $emailList->subscribers()->where('status', 'bounced')->count(),
            'last_subscriber_at' => $emailList->subscribers()->latest('subscribed_at')->value('subscribed_at'),
        ]);
    }
}

