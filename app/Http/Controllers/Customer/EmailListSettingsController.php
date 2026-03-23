<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Services\EmailListService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmailListSettingsController extends Controller
{
    public function __construct(
        protected EmailListService $emailListService
    ) {}

    /**
     * Show the settings form for an email list.
     */
    public function edit(EmailList $list)
    {
        return view('customer.lists.settings', compact('list'));
    }

    /**
     * Update the email list settings.
     */
    public function update(Request $request, EmailList $list)
    {
        $validator = Validator::make($request->all(), [
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'reply_to' => ['nullable', 'email', 'max:255'],
            'double_opt_in' => ['nullable', 'boolean'],
            'default_subject' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string'],
            'welcome_email_enabled' => ['nullable', 'boolean'],
            'welcome_email_subject' => ['nullable', 'string', 'max:255'],
            'welcome_email_content' => ['nullable', 'string'],
            'unsubscribe_email_enabled' => ['nullable', 'boolean'],
            'unsubscribe_email_subject' => ['nullable', 'string', 'max:255'],
            'unsubscribe_email_content' => ['nullable', 'string'],
            'unsubscribe_redirect_url' => ['nullable', 'url', 'max:255'],
            'gdpr_enabled' => ['nullable', 'boolean'],
            'gdpr_text' => ['nullable', 'string'],
            'custom_fields' => ['nullable', 'array'],
            'custom_fields.*.key' => ['nullable', 'string', 'max:50', 'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/'],
            'custom_fields.*.label' => ['nullable', 'string', 'max:100'],
            'custom_fields.*.type' => ['nullable', 'in:text,textarea'],
            'custom_fields.*.required' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $rows = $request->input('custom_fields', []);
            if (!is_array($rows)) {
                return;
            }

            $keys = [];
            foreach ($rows as $idx => $row) {
                if (!is_array($row)) {
                    continue;
                }

                $key = isset($row['key']) ? trim((string) $row['key']) : '';
                $label = isset($row['label']) ? trim((string) $row['label']) : '';

                if ($key === '' && $label === '') {
                    continue;
                }

                if ($key === '' || $label === '') {
                    $validator->errors()->add('custom_fields', 'Each custom field must have both a key and label.');
                    continue;
                }

                $keyLower = strtolower($key);
                if (in_array($keyLower, $keys, true)) {
                    $validator->errors()->add('custom_fields', 'Custom field keys must be unique.');
                    continue;
                }
                $keys[] = $keyLower;
            }
        });

        $validated = $validator->validate();

        $customFields = $request->input('custom_fields', []);
        $normalizedCustomFields = [];
        if (is_array($customFields)) {
            foreach ($customFields as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $key = trim((string) ($row['key'] ?? ''));
                $label = trim((string) ($row['label'] ?? ''));

                if ($key === '' && $label === '') {
                    continue;
                }

                if ($key === '' || $label === '') {
                    continue;
                }

                $normalizedCustomFields[] = [
                    'key' => $key,
                    'label' => $label,
                    'type' => in_array(($row['type'] ?? 'text'), ['text', 'textarea'], true) ? ($row['type'] ?? 'text') : 'text',
                    'required' => (bool) ($row['required'] ?? false),
                ];
            }
        }

        $validated['custom_fields'] = $normalizedCustomFields;

        $this->emailListService->update($list, $validated);

        return redirect()
            ->route('customer.lists.settings', $list)
            ->with('success', 'Settings updated successfully.');
    }
}

