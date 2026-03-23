<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Models\SubscriptionForm;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FormsController extends Controller
{
    private function allowedFieldsForList(EmailList $list): array
    {
        $standard = ['email', 'first_name', 'last_name'];
        $customDefs = $list->custom_fields;
        if (!is_array($customDefs)) {
            $customDefs = [];
        }

        $custom = [];
        foreach ($customDefs as $def) {
            if (!is_array($def)) {
                continue;
            }
            $key = isset($def['key']) ? trim((string) $def['key']) : '';
            if ($key === '') {
                continue;
            }
            $custom[] = 'cf:' . $key;
        }

        return array_values(array_unique(array_merge($standard, $custom)));
    }

    private function defaultFieldMetaForList(EmailList $list): array
    {
        $allowedIcons = ['mail', 'user', 'lock', 'tag', 'message', 'phone'];
        $meta = [];

        foreach ($this->allowedFieldsForList($list) as $fieldKey) {
            $meta[$fieldKey] = [
                'show_label' => true,
                'icon' => $fieldKey === 'email' ? 'mail' : ($fieldKey === 'first_name' || $fieldKey === 'last_name' ? 'user' : ''),
                'icon_color' => '',
                'icon_upload_url' => '',
            ];

            if (!in_array($meta[$fieldKey]['icon'], $allowedIcons, true)) {
                $meta[$fieldKey]['icon'] = '';
            }
        }

        return $meta;
    }

    private function templateSettings(string $templateKey, EmailList $list): array
    {
        $fieldMeta = $this->defaultFieldMetaForList($list);

        $base = [
            'direction' => 'auto',
            'show_title' => true,
            'show_description' => true,
            'field_meta' => $fieldMeta,
            'field_styles' => [],
            'button_styles' => [],
        ];

        if ($templateKey === 'minimal') {
            $base['field_styles'] = [
                'email' => ['normal' => ['border' => '1px solid #e5e7eb', 'border_radius' => '10px', 'padding' => '10px 12px', 'background' => '#ffffff', 'text_color' => '#111827', 'placeholder_color' => '#9ca3af']],
                'first_name' => ['normal' => ['border' => '1px solid #e5e7eb', 'border_radius' => '10px', 'padding' => '10px 12px', 'background' => '#ffffff', 'text_color' => '#111827', 'placeholder_color' => '#9ca3af']],
                'last_name' => ['normal' => ['border' => '1px solid #e5e7eb', 'border_radius' => '10px', 'padding' => '10px 12px', 'background' => '#ffffff', 'text_color' => '#111827', 'placeholder_color' => '#9ca3af']],
            ];
            $base['button_styles'] = [
                'normal' => ['background' => '#111827', 'text_color' => '#ffffff', 'border_radius' => '10px', 'padding' => '10px 14px'],
                'hover' => ['background' => '#000000', 'text_color' => '#ffffff'],
            ];

            return $base;
        }

        if ($templateKey === 'soft') {
            $base['field_styles'] = [
                'email' => ['normal' => ['border' => '1px solid #dbeafe', 'border_radius' => '14px', 'padding' => '11px 14px', 'background' => '#ffffff', 'text_color' => '#0f172a', 'placeholder_color' => '#64748b', 'shadow' => '0 1px 2px rgba(0,0,0,0.06)']],
                'first_name' => ['normal' => ['border' => '1px solid #dbeafe', 'border_radius' => '14px', 'padding' => '11px 14px', 'background' => '#ffffff', 'text_color' => '#0f172a', 'placeholder_color' => '#64748b', 'shadow' => '0 1px 2px rgba(0,0,0,0.06)']],
                'last_name' => ['normal' => ['border' => '1px solid #dbeafe', 'border_radius' => '14px', 'padding' => '11px 14px', 'background' => '#ffffff', 'text_color' => '#0f172a', 'placeholder_color' => '#64748b', 'shadow' => '0 1px 2px rgba(0,0,0,0.06)']],
            ];
            $base['button_styles'] = [
                'normal' => ['background' => '#1E5FEA', 'text_color' => '#ffffff', 'border_radius' => '14px', 'padding' => '11px 14px', 'shadow' => '0 6px 16px rgba(30,95,234,0.22)'],
                'hover' => ['background' => '#174bb8', 'text_color' => '#ffffff'],
            ];

            return $base;
        }

        if ($templateKey === 'dark') {
            $base['field_styles'] = [
                'email' => ['normal' => ['border' => '1px solid #374151', 'border_radius' => '12px', 'padding' => '10px 12px', 'background' => '#111827', 'text_color' => '#f9fafb', 'placeholder_color' => '#9ca3af']],
                'first_name' => ['normal' => ['border' => '1px solid #374151', 'border_radius' => '12px', 'padding' => '10px 12px', 'background' => '#111827', 'text_color' => '#f9fafb', 'placeholder_color' => '#9ca3af']],
                'last_name' => ['normal' => ['border' => '1px solid #374151', 'border_radius' => '12px', 'padding' => '10px 12px', 'background' => '#111827', 'text_color' => '#f9fafb', 'placeholder_color' => '#9ca3af']],
            ];
            $base['button_styles'] = [
                'normal' => ['background' => '#f9fafb', 'text_color' => '#111827', 'border_radius' => '12px', 'padding' => '10px 14px'],
                'hover' => ['background' => '#e5e7eb', 'text_color' => '#111827'],
            ];

            return $base;
        }

        $base['button_styles'] = [
            'normal' => ['background' => '#1E5FEA', 'text_color' => '#ffffff', 'border_radius' => '10px', 'padding' => '10px 14px'],
            'hover' => ['background' => '#174bb8', 'text_color' => '#ffffff'],
        ];

        return $base;
    }

    public function index(Request $request)
    {
        $customer = $request->user('customer');

        $search = trim((string) $request->input('search', ''));

        $forms = SubscriptionForm::query()
            ->whereHas('emailList', function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            })
            ->with('emailList')
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(18)
            ->withQueryString();

        return view('customer.forms.index', compact('forms', 'search'));
    }

    public function create(Request $request)
    {
        $customer = $request->user('customer');

        $lists = EmailList::query()
            ->where('customer_id', $customer->id)
            ->orderBy('name')
            ->get();

        $templates = [
            ['key' => 'default', 'label' => 'Default'],
            ['key' => 'minimal', 'label' => 'Minimal'],
            ['key' => 'soft', 'label' => 'Soft'],
            ['key' => 'dark', 'label' => 'Dark'],
        ];

        return view('customer.forms.create', compact('lists', 'templates'));
    }

    public function store(Request $request)
    {
        $customer = $request->user('customer');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'list_id' => ['required', 'integer'],
            'type' => ['required', 'in:embedded,popup,api'],
            'template' => ['nullable', 'in:default,minimal,soft,dark'],
        ]);

        $list = EmailList::query()
            ->where('customer_id', $customer->id)
            ->where('id', $validated['list_id'])
            ->firstOrFail();

        $templateKey = (string) ($validated['template'] ?? 'default');

        $settings = $this->templateSettings($templateKey, $list);

        $form = SubscriptionForm::create([
            'list_id' => $list->id,
            'name' => $validated['name'],
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'builder' => 'basic',
            'slug' => Str::slug($validated['name']) . '-' . Str::random(8),
            'description' => null,
            'fields' => ['email', 'first_name', 'last_name'],
            'settings' => $settings,
            'gdpr_checkbox' => false,
            'gdpr_text' => null,
            'is_active' => true,
            'html_content' => null,
            'plain_text_content' => null,
            'builder_data' => null,
        ]);

        return redirect()
            ->route('customer.lists.forms.show', [$list, $form])
            ->with('success', 'Subscription form created successfully.');
    }
}
