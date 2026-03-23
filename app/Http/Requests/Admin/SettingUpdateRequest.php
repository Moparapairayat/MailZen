<?php

namespace App\Http\Requests\Admin;

use App\Models\DeliveryServer;
use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingUpdateRequest extends FormRequest
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
        // Dynamic validation based on settings
        $rules = [];
        
        // Get all settings to validate
        $settings = \App\Models\Setting::all();
        
        foreach ($settings as $setting) {
            $rules[$setting->key] = $this->getValidationRule($setting);
        }

        $rules['google_analytics_tracking_id'] = [
            'nullable',
            'string',
            'max:64',
            'regex:/^(G-[A-Z0-9]{4,}|UA-\d{4,}-\d+)$/i',
        ];

        $rules['meta_pixel_id'] = [
            'nullable',
            'string',
            'max:32',
            'regex:/^\d+$/',
        ];

        $rules['default_customer_group_id'] = ['nullable', 'integer', 'exists:customer_groups,id'];
        $rules['new_registered_customer_group_id'] = ['nullable', 'integer', 'exists:customer_groups,id'];

        $rules['billing_currency'] = [
            'nullable',
            'string',
            'size:3',
            'regex:/^[A-Za-z]{3}$/',
        ];

        $rules['brand_color'] = [
            'nullable',
            'string',
            'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/',
        ];

        $rules['toast_position'] = [
            'nullable',
            'string',
            Rule::in(['top_left', 'top_center', 'top_right', 'bottom_left', 'bottom_center', 'bottom_right']),
        ];

        $rules['home_page_variant'] = [
            'nullable',
            'string',
            Rule::in(['all', '1', '2', '3', '4', '5']),
        ];

        $rules['gdpr_notice_title'] = [
            'nullable',
            'string',
            'max:255',
        ];

        $rules['gdpr_notice_description'] = [
            'nullable',
            'string',
            'max:2000',
        ];

        $rules['gdpr_notice_accept_text'] = [
            'nullable',
            'string',
            'max:64',
        ];

        $rules['gdpr_notice_decline_text'] = [
            'nullable',
            'string',
            'max:64',
        ];

        $rules['gdpr_notice_position'] = [
            'nullable',
            'string',
            Rule::in(['bottom_left', 'bottom_right', 'bottom_full_width']),
        ];

        $rules['gdpr_notice_delay_seconds'] = [
            'nullable',
            'integer',
            'min:0',
            'max:3600',
        ];

        $rules['home_redirect_enabled'] = [
            'nullable',
            'boolean',
        ];

        $rules['home_redirect_url'] = [
            'nullable',
            'string',
            'max:2048',
            function (string $attribute, mixed $value, \Closure $fail) {
                $enabled = $this->boolean('home_redirect_enabled');

                if (!$enabled) {
                    return;
                }

                if (!is_string($value) || trim($value) === '') {
                    $fail('Redirect URL is required when Home Redirect is enabled.');
                    return;
                }

                $target = trim((string) $value);

                $parsed = parse_url($target);
                $scheme = is_array($parsed) && is_string($parsed['scheme'] ?? null) ? strtolower((string) $parsed['scheme']) : null;

                if ($scheme !== null && !in_array($scheme, ['http', 'https'], true)) {
                    $fail('Redirect URL must be an http(s) URL or a path starting with /.');
                    return;
                }

                if ($scheme === null && !str_starts_with($target, '/')) {
                    $fail('Redirect URL must start with / when using a relative path.');
                    return;
                }

                if ($target === '/') {
                    $fail('Redirect URL cannot be /.');
                    return;
                }
            },
        ];

        $rules['default_storage_driver'] = [
            'nullable',
            'string',
            Rule::in(['local', 's3', 'wasabi', 'gcs']),
            function (string $attribute, mixed $value, \Closure $fail) {
                if (!is_string($value) || trim($value) === '') {
                    return;
                }

                $driver = strtolower(trim($value));

                $enabledKey = match ($driver) {
                    'local' => 'storage_local_enabled',
                    's3' => 'storage_s3_enabled',
                    'wasabi' => 'storage_wasabi_enabled',
                    'gcs' => 'storage_gcs_enabled',
                    default => null,
                };

                if ($enabledKey && !Setting::get($enabledKey, 0)) {
                    $fail('Selected storage provider is not active. Enable it in Storage settings first.');
                    return;
                }

                $missing = [];

                if ($driver === 's3') {
                    if (!Setting::get('s3_key')) {
                        $missing[] = 'S3 Key';
                    }
                    if (!Setting::get('s3_secret')) {
                        $missing[] = 'S3 Secret';
                    }
                    if (!Setting::get('s3_region')) {
                        $missing[] = 'S3 Region';
                    }
                    if (!Setting::get('s3_bucket')) {
                        $missing[] = 'S3 Bucket';
                    }
                }

                if ($driver === 'wasabi') {
                    if (!Setting::get('wasabi_key')) {
                        $missing[] = 'Wasabi Key';
                    }
                    if (!Setting::get('wasabi_secret')) {
                        $missing[] = 'Wasabi Secret';
                    }
                    if (!Setting::get('wasabi_region')) {
                        $missing[] = 'Wasabi Region';
                    }
                    if (!Setting::get('wasabi_bucket')) {
                        $missing[] = 'Wasabi Bucket';
                    }
                    if (!Setting::get('wasabi_endpoint')) {
                        $missing[] = 'Wasabi Endpoint';
                    }
                }

                if ($driver === 'gcs') {
                    if (!Setting::get('gcs_project_id')) {
                        $missing[] = 'GCS Project ID';
                    }
                    if (!Setting::get('gcs_bucket')) {
                        $missing[] = 'GCS Bucket';
                    }
                    if (!Setting::get('gcs_key_file')) {
                        $missing[] = 'GCS Key File';
                    }
                }

                if (!empty($missing)) {
                    $fail('Selected storage provider is not configured: ' . implode(', ', $missing) . '.');
                }
            },
        ];

        $rules['app_logo'] = ['nullable', 'image', 'max:2048'];
        $rules['app_logo_dark'] = [
            'nullable',
            Rule::requiredIf(function () {
                $category = (string) $this->input('category', 'general');
                $category = trim($category);

                if ($category !== 'general') {
                    return false;
                }

                $existing = Setting::get('app_logo_dark');

                return !is_string($existing) || trim((string) $existing) === '';
            }),
            'image',
            'max:2048',
        ];
        $rules['site_favicon'] = ['nullable', 'file', 'mimes:png,jpg,jpeg,webp,gif,ico', 'max:2048'];
        $rules['public_meta_image'] = ['nullable', 'image', 'max:2048'];

        $deliveryServerSettingKeys = [
            'transactional_delivery_server_id',
            'verification_delivery_server_id',
            'password_reset_delivery_server_id',
        ];

        foreach ($deliveryServerSettingKeys as $key) {
            $rules[$key] = [
                'nullable',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($key) {
                    if ($value === null) {
                        return;
                    }

                    if (!is_string($value)) {
                        $fail('Invalid delivery server selection.');
                        return;
                    }

                    $v = trim($value);
                    if ($v === '') {
                        return;
                    }

                    if ($v === 'system') {
                        return;
                    }

                    if ($key !== 'transactional_delivery_server_id' && $v === 'inherit') {
                        return;
                    }

                    if (!ctype_digit($v)) {
                        $fail('Invalid delivery server selection.');
                        return;
                    }

                    $id = (int) $v;
                    if ($id <= 0 || !DeliveryServer::query()->whereKey($id)->whereNull('customer_id')->exists()) {
                        $fail('Selected delivery server does not exist or is not a system-wide server.');
                    }
                },
            ];
        }
        
        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $currency = $this->input('billing_currency');
        if (is_string($currency)) {
            $this->merge([
                'billing_currency' => strtoupper(trim($currency)),
            ]);
        }
    }

    /**
     * Get validation rule for a setting based on its type.
     */
    protected function getValidationRule(\App\Models\Setting $setting): array
    {
        $rules = ['nullable'];

        switch ($setting->type) {
            case 'integer':
                $rules[] = 'integer';
                break;
            case 'boolean':
                $rules[] = 'boolean';
                break;
            case 'json':
            case 'array':
                $rules[] = 'json';
                break;
            case 'email':
                $rules[] = 'email';
                break;
            case 'url':
                $rules[] = 'url';
                break;
            default:
                $rules[] = 'string';
        }

        return $rules;
    }
}


