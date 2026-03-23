<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CustomerGroupStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:customer_groups,name'],
            'description' => ['nullable', 'string'],
            'is_system' => ['boolean'],

            'allocated_delivery_server_ids' => ['nullable', 'array'],
            'allocated_delivery_server_ids.*' => ['integer', 'exists:delivery_servers,id'],

            'messages.access.default' => ['nullable', 'string'],
            'messages.access.campaigns.features.ab_testing' => ['nullable', 'string'],
            'messages.access.autoresponders.enabled' => ['nullable', 'string'],
            'messages.access.domains.tracking_domains.can_manage' => ['nullable', 'string'],
            'messages.access.domains.sending_domains.can_manage' => ['nullable', 'string'],
            'messages.access.servers.permissions.can_add_delivery_servers' => ['nullable', 'string'],

            'messages.limits.lists.limits.max_lists' => ['nullable', 'string'],
            'messages.limits.campaigns.limits.max_campaigns' => ['nullable', 'string'],
            'messages.limits.autoresponders.max_autoresponders' => ['nullable', 'string'],
            'messages.limits.domains.tracking_domains.max_tracking_domains' => ['nullable', 'string'],
            'messages.limits.domains.sending_domains.max_sending_domains' => ['nullable', 'string'],

            'ai.must_use_own_keys' => ['boolean'],
            'ai.token_limit' => ['nullable', 'integer', 'min:0'],
            'ai.image_credits' => ['nullable', 'integer', 'min:0'],
            
            // General
            'general.group_name' => ['nullable', 'string'],
            'general.show_articles_menu' => ['boolean'],
            'general.mask_email_addresses' => ['boolean'],
            'general.notification_frequency' => ['nullable', 'in:disabled,daily,weekly,monthly'],
            'general.notification_message' => ['nullable', 'string'],
            
            // Servers
            'servers.limits.max_delivery_servers' => ['nullable', 'integer', 'min:0'],
            'servers.limits.max_bounce_servers' => ['nullable', 'integer', 'min:0'],
            'servers.limits.max_reply_servers' => ['nullable', 'integer', 'min:0'],
            'servers.limits.max_feedback_loop_servers' => ['nullable', 'integer', 'min:0'],
            'servers.limits.max_email_box_monitors' => ['nullable', 'integer', 'min:0'],
            'servers.permissions.must_add_bounce_server' => ['boolean'],
            'servers.permissions.must_add_reply_server' => ['boolean'],
            'servers.permissions.must_add_delivery_server' => ['boolean'],
            'servers.permissions.can_add_delivery_servers' => ['boolean'],
            'servers.permissions.can_access_bounce_servers' => ['boolean'],
            'servers.permissions.can_add_bounce_servers' => ['boolean'],
            'servers.permissions.can_edit_bounce_servers' => ['boolean'],
            'servers.permissions.can_delete_bounce_servers' => ['boolean'],
            'servers.permissions.can_access_reply_servers' => ['boolean'],
            'servers.permissions.can_add_reply_servers' => ['boolean'],
            'servers.permissions.can_edit_reply_servers' => ['boolean'],
            'servers.permissions.can_delete_reply_servers' => ['boolean'],
            'servers.permissions.can_select_delivery_servers_for_campaigns' => ['boolean'],
            'servers.permissions.can_use_system_servers' => ['boolean'],
            'servers.custom_headers' => ['nullable', 'string'],
            
            // Domains
            'domains.tracking_domains.can_manage' => ['boolean'],
            'domains.tracking_domains.select_for_servers' => ['boolean'],
            'domains.tracking_domains.select_for_campaigns' => ['boolean'],
            'domains.tracking_domains.must_add' => ['boolean'],
            'domains.tracking_domains.max_tracking_domains' => ['nullable', 'integer', 'min:0'],
            'domains.sending_domains.can_manage' => ['boolean'],
            'domains.sending_domains.must_add' => ['boolean'],
            'domains.sending_domains.max_sending_domains' => ['nullable', 'integer', 'min:0'],
            
            // Lists
            'lists.permissions.*' => ['boolean'],
            'lists.limits.*' => ['nullable', 'integer', 'min:0'],
            'lists.limits.max_forms_per_list' => ['nullable', 'integer', 'min:0'],
            'lists.optin.force_optin' => ['nullable', 'in:,single,double'],
            'lists.optin.force_optout' => ['nullable', 'in:,single,double'],
            'lists.optin.force_double_optin_confirmation' => ['boolean'],
            'lists.blacklist_behavior.*' => ['boolean'],
            
            // Campaigns
            'campaigns.limits.*' => ['nullable', 'integer', 'min:0'],
            'campaigns.features.*' => ['boolean'],
            'campaigns.features.ab_testing' => ['boolean'],
            'campaigns.permissions.*' => ['boolean'],
            'campaigns.analytics.*' => ['boolean'],
            'campaigns.complaint_limits.*' => ['nullable', 'numeric', 'min:0'],
            'campaigns.headers.*' => ['nullable', 'string'],

            // Auto Responders
            'autoresponders.enabled' => ['boolean'],
            'autoresponders.max_autoresponders' => ['nullable', 'integer', 'min:0'],

            'automations.enabled' => ['boolean'],
            
            // Surveys
            'surveys.limits.*' => ['nullable', 'integer', 'min:0'],
            'surveys.permissions.*' => ['boolean'],
            
            // Sending Quota
            'sending_quota.quota' => ['nullable', 'integer', 'min:0'],
            'sending_quota.time_value' => ['nullable', 'integer', 'min:1'],
            'sending_quota.time_unit' => ['nullable', 'in:minute,hour,day,week,month,year'],
            'sending_quota.wait_for_expire' => ['boolean'],
            'sending_quota.action_on_reach' => ['nullable', 'in:none,reset,move_group'],
            'sending_quota.move_to_group_id' => ['nullable', 'integer', 'exists:customer_groups,id'],
            'sending_quota.hourly_quota' => ['nullable', 'integer', 'min:0'],
            'sending_quota.daily_quota' => ['nullable', 'integer', 'min:0'],
            'sending_quota.weekly_quota' => ['nullable', 'integer', 'min:0'],
            'sending_quota.monthly_quota' => ['nullable', 'integer', 'min:0'],
            'sending_quota.notifications.enable' => ['boolean'],
            'sending_quota.notifications.percent_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
            'sending_quota.notifications.email_template' => ['nullable', 'string'],
            
            // Usage Counters
            'usage_counters.*' => ['boolean'],
            
            // CDN
            'cdn.enabled' => ['boolean'],
            'cdn.subdomain' => ['nullable', 'string'],
            'cdn.use_for_assets' => ['boolean'],
            
            // API
            'api.enabled' => ['boolean'],
            
            // Subaccounts
            'subaccounts.enabled' => ['boolean'],
            'subaccounts.max_subaccounts' => ['nullable', 'integer', 'min:0'],
            
            // Landing Pages
            'landing_pages.max_landing_pages' => ['nullable', 'integer', 'min:0'],

            // Email Validation
            'email_validation.access' => ['boolean'],
            'email_validation.must_add' => ['boolean'],
            'email_validation.max_tools' => ['nullable', 'integer', 'min:0'],
            'email_validation.monthly_limit' => ['nullable', 'integer', 'min:0'],

            // Integrations
            'integrations.permissions.can_access_google' => ['boolean'],
        ];
    }
}

