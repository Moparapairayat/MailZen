<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerUpdateRequest extends FormRequest
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
        $customer = $this->route('customer');
        $customerId = is_object($customer) ? ($customer->id ?? null) : $customer;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'timezone' => ['nullable', 'string', 'max:255'],
            'language' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:active,inactive,pending,suspended'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
            'quota' => ['nullable', 'numeric', 'min:0'],
            'max_lists' => ['nullable', 'integer', 'min:0'],
            'max_subscribers' => ['nullable', 'integer', 'min:0'],
            'max_campaigns' => ['nullable', 'integer', 'min:0'],
            'expires_at' => ['nullable', 'date'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['exists:customer_groups,id'],

            'allocated_delivery_server_ids' => ['nullable', 'array'],
            'allocated_delivery_server_ids.*' => ['integer', 'exists:delivery_servers,id'],

            'tax_id' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_1' => ['nullable', 'string', 'max:255'],
            'billing_address.address_line_2' => ['nullable', 'string', 'max:255'],
            'billing_address.city' => ['nullable', 'string', 'max:255'],
            'billing_address.state' => ['nullable', 'string', 'max:255'],
            'billing_address.postal_code' => ['nullable', 'string', 'max:255'],
            'billing_address.country' => ['nullable', 'string', 'max:255'],
        ];
    }
}

