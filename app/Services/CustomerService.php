<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Setting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerService
{
    /**
     * Get paginated list of customers.
     */
    public function getPaginated(array $filters = [], int $perPage = 15, string $pageName = 'page'): LengthAwarePaginator
    {
        $query = Customer::query()->with('customerGroups');

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['customer_group_id'])) {
            $query->whereHas('customerGroups', function ($q) use ($filters) {
                $q->where('customer_groups.id', $filters['customer_group_id']);
            });
        }

        return $query->latest()->paginate($perPage, ['*'], $pageName);
    }

    /**
     * Create a new customer.
     */
    public function create(array $data): Customer
    {
        // Handle avatar upload
        $avatarPath = null;
        if (isset($data['avatar']) && $data['avatar']->isValid()) {
            $avatarPath = $data['avatar']->store('avatars', 'public');
        }

        $customerData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'timezone' => $data['timezone'] ?? 'UTC',
            'language' => $data['language'] ?? 'en',
            'status' => $data['status'] ?? 'pending',
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'country' => $data['country'] ?? null,
            'currency' => $data['currency'] ?? Setting::get('billing_currency', 'USD'),
            'quota' => $data['quota'] ?? 0,
            'max_lists' => $data['max_lists'] ?? ($data['max_lists'] ?? 10),
            'max_subscribers' => $data['max_subscribers'] ?? 0,
            'max_campaigns' => $data['max_campaigns'] ?? 0,
            'expires_at' => isset($data['expires_at']) ? $data['expires_at'] : null,
        ];

        // Add new fields if they exist in the model's fillable array
        // Note: These fields may need to be added to the database migration
        $optionalFields = [
            'birth_date',
            'parent_account',
            'auto_deactivate_at',
            'send_details_via_email',
            'two_factor_auth_enabled',
            'security_notes',
            'monthly_sending_limit',
            'daily_sending_limit',
            'max_campaigns_per_day',
            'welcome_campaign',
            'plan_id',
            'renewal_type',
            'tax_id',
            'marketing_emails',
            'transactional_emails',
            'sms_notifications',
        ];

        foreach ($optionalFields as $field) {
            if (isset($data[$field])) {
                $customerData[$field] = $data[$field];
            }
        }

        // Store avatar path if uploaded
        if ($avatarPath) {
            $customerData['avatar_path'] = $avatarPath;
        }

        // Store complex data as JSON (if columns exist)
        if (isset($data['security']['ip_restrictions']) && is_array($data['security']['ip_restrictions'])) {
            $customerData['ip_restrictions'] = json_encode(array_filter($data['security']['ip_restrictions']));
        }

        if (isset($data['automation']['auto_tagging_rules']) && is_array($data['automation']['auto_tagging_rules'])) {
            $customerData['auto_tagging_rules'] = json_encode($data['automation']['auto_tagging_rules']);
        }

        if (isset($data['preferred_channels']) && is_array($data['preferred_channels'])) {
            $customerData['preferred_channels'] = json_encode($data['preferred_channels']);
        }

        if (isset($data['roles']) && is_array($data['roles'])) {
            $customerData['roles'] = json_encode($data['roles']);
        }

        if (isset($data['billing_address']) && is_array($data['billing_address'])) {
            $customerData['billing_address'] = $data['billing_address'];
        }

        $customer = Customer::create($customerData);

        // Attach customer groups (support both old and new field names)
        $groupIds = [];
        if (isset($data['customer_group_ids']) && is_array($data['customer_group_ids'])) {
            $groupIds = $data['customer_group_ids'];
        } elseif (isset($data['group_id'])) {
            $groupIds = [$data['group_id']];
        }

        if (!empty($groupIds)) {
            $customer->customerGroups()->sync($groupIds);
        }

        if (isset($data['allocated_delivery_server_ids']) && is_array($data['allocated_delivery_server_ids'])) {
            $customer->allocatedDeliveryServers()->sync($data['allocated_delivery_server_ids']);
        }

        return $customer->load('customerGroups');
    }

    /**
     * Update an existing customer.
     */
    public function update(Customer $customer, array $data): Customer
    {
        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'timezone' => $data['timezone'] ?? $customer->timezone,
            'language' => $data['language'] ?? $customer->language,
            'status' => $data['status'] ?? $customer->status,
            'company_name' => $data['company_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'country' => $data['country'] ?? null,
            'currency' => $data['currency'] ?? $customer->currency,
            'quota' => $data['quota'] ?? $customer->quota,
            'max_lists' => $data['max_lists'] ?? $customer->max_lists,
            'max_subscribers' => $data['max_subscribers'] ?? $customer->max_subscribers,
            'max_campaigns' => $data['max_campaigns'] ?? $customer->max_campaigns,
            'expires_at' => isset($data['expires_at']) ? $data['expires_at'] : $customer->expires_at,
        ];

        if (array_key_exists('tax_id', $data)) {
            $updateData['tax_id'] = $data['tax_id'];
        }

        if (isset($data['billing_address']) && is_array($data['billing_address'])) {
            $updateData['billing_address'] = $data['billing_address'];
        }

        // Update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $customer->update($updateData);

        // Sync customer groups
        if (isset($data['customer_group_ids']) && is_array($data['customer_group_ids'])) {
            $customer->customerGroups()->sync($data['customer_group_ids']);
        }

        if (isset($data['allocated_delivery_server_ids']) && is_array($data['allocated_delivery_server_ids'])) {
            $customer->allocatedDeliveryServers()->sync($data['allocated_delivery_server_ids']);
        }

        return $customer->load('customerGroups');
    }

    /**
     * Delete a customer.
     */
    public function delete(Customer $customer): bool
    {
        return (bool) DB::transaction(function () use ($customer) {
            $customer->customerGroups()->detach();
            $customer->forceFill([
                'email' => $this->deletedEmail((string) $customer->email, (int) $customer->id),
            ])->save();

            return $customer->delete();
        });
    }

    private function deletedEmail(string $email, int $id): string
    {
        $timestamp = time();

        return "deleted+{$id}+{$timestamp}@mailzen.invalid";
    }

    /**
     * Get all customer groups for select options.
     */
    public function getCustomerGroupsForSelect(): array
    {
        return CustomerGroup::orderBy('name')->pluck('name', 'id')->toArray();
    }

    /**
     * Get timezones for select options.
     */
    public function getTimezones(): array
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    /**
     * Get languages for select options.
     */
    public function getLanguages(): array
    {
        return [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'hi' => 'Hindi',
        ];
    }

    /**
     * Get plans for select options.
     */
    public function getPlans(): array
    {
        // This would typically come from a plans table or configuration
        // For now, returning a basic structure
        return [
            'free' => 'Free',
            'starter' => 'Starter',
            'professional' => 'Professional',
            'enterprise' => 'Enterprise',
        ];
    }
}
