<?php

namespace App\Services;

use App\Models\Template;
use App\Models\Customer;
use App\Models\CustomerGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TemplateService
{
    /**
     * Get paginated list of templates for a customer.
     */
    public function getPaginated(Customer $customer, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        // Get customer's group IDs
        $groupIds = $customer->customerGroups()->pluck('customer_groups.id')->values()->all();

        $query = Template::where(function ($q) use ($customer, $groupIds) {
            $q->where('customer_id', $customer->id)
              ->orWhere(function ($subQ) use ($groupIds) {
                  // Show public templates that are either:
                  // 1. Not restricted to specific groups, OR
                  // 2. Restricted to groups that include the customer's groups
                  $subQ->where('is_public', true)
                       ->where('is_system', false)
                       ->where(function ($groupQuery) use ($groupIds) {
                           $groupQuery->whereDoesntHave('customerGroups') // No restrictions
                                    ->orWhereHas('customerGroups', function ($restrictedQuery) use ($groupIds) {
                                        $restrictedQuery->whereIn('customer_groups.id', $groupIds);
                                    });
                       });
              });
        });

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new template.
     */
    public function create(Customer $customer, array $data): Template
    {
        $settings = [];
        if (isset($data['settings']) && is_array($data['settings'])) {
            $settings = $data['settings'];
        }

        return Template::create([
            'customer_id' => $customer->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'email',
            'html_content' => $data['html_content'] ?? null,
            'plain_text_content' => $data['plain_text_content'] ?? null,
            'grapesjs_data' => $data['grapesjs_data'] ?? null,
            'settings' => $settings,
            'is_public' => $data['is_public'] ?? false,
        ]);
    }

    /**
     * Update an existing template.
     */
    public function update(Template $template, array $data): Template
    {
        $settings = is_array($template->settings) ? $template->settings : [];
        if (isset($data['settings']) && is_array($data['settings'])) {
            $settings = array_replace($settings, $data['settings']);
        }

        $template->update([
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'type' => $data['type'] ?? $template->type,
            'html_content' => $data['html_content'] ?? $template->html_content,
            'plain_text_content' => $data['plain_text_content'] ?? $template->plain_text_content,
            'grapesjs_data' => $data['grapesjs_data'] ?? $template->grapesjs_data,
            'settings' => $settings,
            'is_public' => $data['is_public'] ?? $template->is_public,
        ]);

        return $template->fresh();
    }

    /**
     * Delete a template.
     */
    public function delete(Template $template): bool
    {
        // Don't allow deletion of system templates
        if ($template->is_system) {
            throw new \Exception('Cannot delete system templates.');
        }

        return $template->delete();
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(Template $template, Customer $customer): Template
    {
        // Check if customer has access to this template
        if (!$this->canAccessTemplate($template, $customer)) {
            throw new \Exception('You do not have permission to duplicate this template.');
        }

        $newTemplate = $template->replicate();
        $newTemplate->customer_id = $customer->id;
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->slug = null; // Will be auto-generated
        $newTemplate->is_public = false;
        $newTemplate->is_system = false;
        $newTemplate->usage_count = 0;
        $newTemplate->save();

        return $newTemplate;
    }

    /**
     * Check if customer can access a template.
     */
    public function canAccessTemplate(Template $template, Customer $customer): bool
    {
        // Customer can always access their own templates
        if ($template->customer_id === $customer->id) {
            return true;
        }

        // System templates are accessible to everyone
        if ($template->is_system) {
            return true;
        }

        // Public templates need group visibility check
        if ($template->is_public) {
            $groupIds = $customer->customerGroups()->pluck('customer_groups.id')->values()->all();
            
            // If template has no group restrictions, it's visible to all
            if (!$template->customerGroups()->exists()) {
                return true;
            }
            
            // Check if customer's groups are in the template's allowed groups
            return $template->customerGroups()
                ->whereIn('customer_groups.id', $groupIds)
                ->exists();
        }

        return false;
    }
}

