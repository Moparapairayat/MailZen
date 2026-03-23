<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class CustomerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Basic Info
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'confirm_email' => ['required', 'email', 'same:email'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['required', 'string', 'same:password'],
            
            // Profile
            'timezone' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            
            // Account Settings
            'status' => ['required', 'in:active,inactive,pending,suspended'],
            'send_details_via_email' => ['nullable', 'boolean'],
            'parent_account' => ['nullable', 'string', 'max:255'],
            'auto_deactivate_at' => ['nullable', 'date'],
            
            // Group Membership
            'group_id' => ['nullable', 'exists:customer_groups,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['in:viewer,sender,manager,admin'],

            'allocated_delivery_server_ids' => ['nullable', 'array'],
            'allocated_delivery_server_ids.*' => ['integer', 'exists:delivery_servers,id'],
            
            // Contact Preferences
            'marketing_emails' => ['nullable', 'boolean'],
            'transactional_emails' => ['nullable', 'boolean'],
            'sms_notifications' => ['nullable', 'boolean'],
            'preferred_channels' => ['nullable', 'array'],
            'preferred_channels.*' => ['in:email,sms,push,whatsapp'],
            
            // Security
            'two_factor_auth_enabled' => ['nullable', 'boolean'],
            'security_notes' => ['nullable', 'string', 'max:1000'],
            'security.ip_restrictions' => ['nullable', 'array'],
            'security.ip_restrictions.*' => ['ip'],
            
            // Limits
            'monthly_sending_limit' => ['nullable', 'integer', 'min:0'],
            'daily_sending_limit' => ['nullable', 'integer', 'min:0'],
            'max_lists' => ['nullable', 'integer', 'min:0'],
            'max_campaigns_per_day' => ['nullable', 'integer', 'min:0'],
            
            // Automation
            'welcome_campaign' => ['nullable', 'boolean'],
            'automation.auto_tagging_rules' => ['nullable', 'array'],
            'automation.auto_tagging_rules.*.trigger' => ['required_with:automation.auto_tagging_rules.*.tag', 'string', 'max:255'],
            'automation.auto_tagging_rules.*.tag' => ['required_with:automation.auto_tagging_rules.*.trigger', 'string', 'max:255'],
            
            // Billing
            'plan_id' => ['nullable', 'string', 'max:255'],
            'renewal_type' => ['nullable', 'in:monthly,yearly'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_1' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['nullable', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:255'],
            'billing_address.country' => ['nullable', 'string', 'max:255'],
            
            // Legacy fields (for backward compatibility)
            'company_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'quota' => ['nullable', 'numeric', 'min:0'],
            'max_subscribers' => ['nullable', 'integer', 'min:0'],
            'max_campaigns' => ['nullable', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['exists:customer_groups,id'],
        ];
    }
}

