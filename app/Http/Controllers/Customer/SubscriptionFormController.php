<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailList;
use App\Models\SubscriptionForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubscriptionFormController extends Controller
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

    private function normalizeSelectedFields(EmailList $list, mixed $selected): array
    {
        $allowed = $this->allowedFieldsForList($list);
        $selectedArr = is_array($selected) ? $selected : [];
        $normalized = [];

        foreach ($selectedArr as $value) {
            $v = trim((string) $value);
            if ($v === '') {
                continue;
            }
            if (!in_array($v, $allowed, true)) {
                continue;
            }
            $normalized[] = $v;
        }

        if (!in_array('email', $normalized, true)) {
            $normalized[] = 'email';
        }

        return array_values(array_unique($normalized));
    }

    private function normalizeSelectedFieldsWithOrder(EmailList $list, mixed $order, mixed $enabled): array
    {
        $allowed = $this->allowedFieldsForList($list);
        $orderArr = is_array($order) ? $order : [];
        $enabledArr = is_array($enabled) ? $enabled : [];

        $enabledSet = [];
        foreach ($enabledArr as $value) {
            $v = trim((string) $value);
            if ($v !== '') {
                $enabledSet[$v] = true;
            }
        }

        $normalized = [];
        foreach ($orderArr as $value) {
            $v = trim((string) $value);
            if ($v === '') {
                continue;
            }
            if (!in_array($v, $allowed, true)) {
                continue;
            }
            if ($v === 'email' || isset($enabledSet[$v])) {
                $normalized[] = $v;
            }
        }

        if (!in_array('email', $normalized, true)) {
            array_unshift($normalized, 'email');
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Display a listing of forms for a list.
     */
    public function index(EmailList $list)
    {
        $forms = $list->subscriptionForms()->latest()->get();

        return view('customer.lists.forms.index', compact('list', 'forms'));
    }

    /**
     * Show the form for creating a new subscription form.
     */
    public function create(EmailList $list)
    {
        return view('customer.lists.forms.create', compact('list'));
    }

    /**
     * Store a newly created subscription form.
     */
    public function store(Request $request, EmailList $list)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:embedded,popup,api'],
            'description' => ['nullable', 'string'],
            'fields' => ['nullable', 'array'],
            'fields_order' => ['nullable', 'array'],
            'fields_enabled' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'settings.direction' => ['nullable', 'in:auto,ltr,rtl'],
            'settings.popup_delay_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'settings.popup_show_once' => ['nullable', 'boolean'],
            'settings.popup_width' => ['nullable', 'integer', 'min:200', 'max:1400'],
            'settings.popup_height' => ['nullable', 'integer', 'min:200', 'max:1400'],
            'settings.popup_bg_color' => ['nullable', 'string'],
            'settings.popup_overlay_color' => ['nullable', 'string'],
            'gdpr_checkbox' => ['nullable', 'boolean'],
            'gdpr_text' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $settings = $request->input('settings', []);
        $settings['show_title'] = (bool) ($settings['show_title'] ?? true);
        $settings['show_description'] = (bool) ($settings['show_description'] ?? true);

        $fieldMetaInput = $settings['field_meta'] ?? [];
        if (!is_array($fieldMetaInput)) {
            $fieldMetaInput = [];
        }
        $allowedIcons = ['mail', 'user', 'lock', 'tag', 'message', 'phone'];
        $fieldMeta = [];
        foreach ($this->allowedFieldsForList($list) as $fieldKey) {
            $meta = is_array($fieldMetaInput[$fieldKey] ?? null) ? $fieldMetaInput[$fieldKey] : [];
            $showLabel = (bool) ($meta['show_label'] ?? true);
            $icon = isset($meta['icon']) ? trim((string) $meta['icon']) : '';
            if ($icon !== '' && !in_array($icon, $allowedIcons, true)) {
                $icon = '';
            }
            $iconColor = isset($meta['icon_color']) ? trim((string) $meta['icon_color']) : '';
            if ($iconColor !== '' && !preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $iconColor)) {
                $iconColor = '';
            }

            $iconUploadUrl = isset($meta['icon_upload_url']) ? trim((string) $meta['icon_upload_url']) : '';
            if ($iconUploadUrl !== '' && !preg_match('~^(https?://|/)[^\s<>"]+$~i', $iconUploadUrl)) {
                $iconUploadUrl = '';
            }
            $fieldMeta[$fieldKey] = [
                'show_label' => $showLabel,
                'icon' => $icon,
                'icon_color' => $iconColor,
                'icon_upload_url' => $iconUploadUrl,
            ];
        }
        $settings['field_meta'] = $fieldMeta;

        $dir = (string) ($settings['direction'] ?? 'ltr');
        if (!in_array($dir, ['auto', 'ltr', 'rtl'], true)) {
            $dir = 'ltr';
        }
        $settings['direction'] = $dir;
        $settings['popup_delay_seconds'] = max(0, (int) ($settings['popup_delay_seconds'] ?? 5));
        $settings['popup_show_once'] = (bool) ($settings['popup_show_once'] ?? false);
        $settings['popup_width'] = (int) ($settings['popup_width'] ?? 600);
        $settings['popup_height'] = (int) ($settings['popup_height'] ?? 420);
        $settings['popup_bg_color'] = (string) ($settings['popup_bg_color'] ?? '#ffffff');
        $settings['popup_overlay_color'] = (string) ($settings['popup_overlay_color'] ?? '#000000');

        $fields = null;
        if (is_array($request->input('fields_order')) && is_array($request->input('fields_enabled'))) {
            $fields = $this->normalizeSelectedFieldsWithOrder($list, $request->input('fields_order'), $request->input('fields_enabled'));
        } else {
            $fields = $this->normalizeSelectedFields($list, $request->input('fields', ['email', 'first_name', 'last_name']));
        }

        $form = SubscriptionForm::create([
            'list_id' => $list->id,
            'name' => $validated['name'],
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'builder' => 'basic',
            'slug' => Str::slug($validated['name']) . '-' . Str::random(8),
            'description' => $validated['description'] ?? null,
            'fields' => $fields,
            'settings' => $settings,
            'gdpr_checkbox' => $validated['gdpr_checkbox'] ?? false,
            'gdpr_text' => $validated['gdpr_text'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'html_content' => null,
            'plain_text_content' => null,
            'builder_data' => null,
        ]);

        return redirect()
            ->route('customer.lists.forms.show', [$list, $form])
            ->with('success', 'Subscription form created successfully.');
    }

    /**
     * Display the specified subscription form.
     */
    public function show(EmailList $list, SubscriptionForm $form)
    {
        return view('customer.lists.forms.show', compact('list', 'form'));
    }

    /**
     * Show the form for editing the specified subscription form.
     */
    public function edit(EmailList $list, SubscriptionForm $form)
    {
        if ((int) $form->list_id !== (int) $list->id) {
            abort(404);
        }

        return view('customer.lists.forms.edit', compact('list', 'form'));
    }

    /**
     * Update the specified subscription form.
     */
    public function update(Request $request, EmailList $list, SubscriptionForm $form)
    {
        if ((int) $form->list_id !== (int) $list->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:embedded,popup,api'],
            'description' => ['nullable', 'string'],
            'fields' => ['nullable', 'array'],
            'fields_order' => ['nullable', 'array'],
            'fields_enabled' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'settings.direction' => ['nullable', 'in:auto,ltr,rtl'],
            'settings.popup_delay_seconds' => ['nullable', 'integer', 'min:0', 'max:3600'],
            'settings.popup_show_once' => ['nullable', 'boolean'],
            'settings.popup_width' => ['nullable', 'integer', 'min:200', 'max:1400'],
            'settings.popup_height' => ['nullable', 'integer', 'min:200', 'max:1400'],
            'settings.popup_bg_color' => ['nullable', 'string'],
            'settings.popup_overlay_color' => ['nullable', 'string'],
            'gdpr_checkbox' => ['nullable', 'boolean'],
            'gdpr_text' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $settings = $request->input('settings', $form->settings ?? []);
        $settings['show_title'] = (bool) ($settings['show_title'] ?? true);
        $settings['show_description'] = (bool) ($settings['show_description'] ?? true);

        $fieldMetaInput = $settings['field_meta'] ?? [];
        if (!is_array($fieldMetaInput)) {
            $fieldMetaInput = [];
        }
        $allowedIcons = ['mail', 'user', 'lock', 'tag', 'message', 'phone'];
        $fieldMeta = [];
        foreach ($this->allowedFieldsForList($list) as $fieldKey) {
            $meta = is_array($fieldMetaInput[$fieldKey] ?? null) ? $fieldMetaInput[$fieldKey] : [];
            $showLabel = (bool) ($meta['show_label'] ?? true);
            $icon = isset($meta['icon']) ? trim((string) $meta['icon']) : '';
            if ($icon !== '' && !in_array($icon, $allowedIcons, true)) {
                $icon = '';
            }
            $iconColor = isset($meta['icon_color']) ? trim((string) $meta['icon_color']) : '';
            if ($iconColor !== '' && !preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $iconColor)) {
                $iconColor = '';
            }

            $iconUploadUrl = isset($meta['icon_upload_url']) ? trim((string) $meta['icon_upload_url']) : '';
            if ($iconUploadUrl !== '' && !preg_match('~^(https?://|/)[^\s<>"]+$~i', $iconUploadUrl)) {
                $iconUploadUrl = '';
            }
            $fieldMeta[$fieldKey] = [
                'show_label' => $showLabel,
                'icon' => $icon,
                'icon_color' => $iconColor,
                'icon_upload_url' => $iconUploadUrl,
            ];
        }
        $settings['field_meta'] = $fieldMeta;

        $dir = (string) ($settings['direction'] ?? 'ltr');
        if (!in_array($dir, ['auto', 'ltr', 'rtl'], true)) {
            $dir = 'ltr';
        }
        $settings['direction'] = $dir;
        $settings['popup_delay_seconds'] = max(0, (int) ($settings['popup_delay_seconds'] ?? 5));
        $settings['popup_show_once'] = (bool) ($settings['popup_show_once'] ?? false);
        $settings['popup_width'] = (int) ($settings['popup_width'] ?? 600);
        $settings['popup_height'] = (int) ($settings['popup_height'] ?? 420);
        $settings['popup_bg_color'] = (string) ($settings['popup_bg_color'] ?? '#ffffff');
        $settings['popup_overlay_color'] = (string) ($settings['popup_overlay_color'] ?? '#000000');

        $updateData = $validated;
        if (is_array($request->input('fields_order')) && is_array($request->input('fields_enabled'))) {
            $updateData['fields'] = $this->normalizeSelectedFieldsWithOrder($list, $request->input('fields_order'), $request->input('fields_enabled'));
        } else {
            $updateData['fields'] = $this->normalizeSelectedFields($list, $request->input('fields', $form->fields));
        }
        $updateData['settings'] = $settings;
        $updateData['builder'] = 'basic';
        $updateData['html_content'] = null;
        $updateData['plain_text_content'] = null;
        $updateData['builder_data'] = null;
        $form->update($updateData);

        return redirect()
            ->route('customer.lists.forms.show', [$list, $form])
            ->with('success', 'Subscription form updated successfully.');
    }

    public function uploadIcon(Request $request, EmailList $list)
    {
        $customer = auth('customer')->user();
        if (!$customer || (int) $list->customer_id !== (int) $customer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'icon' => ['required', 'file', 'max:2048', 'mimetypes:image/png,image/svg+xml'],
        ]);

        $file = $validated['icon'];
        $path = $file->storePublicly('form-icons', 'public');
        $url = Storage::disk('public')->url($path);
        $url = preg_replace('~(?<!:)//+~', '/', $url);

        return response()->json([
            'url' => $url,
        ]);
    }

    /**
     * Remove the specified subscription form.
     */
    public function destroy(EmailList $list, SubscriptionForm $form)
    {
        if ((int) $form->list_id !== (int) $list->id) {
            abort(404);
        }

        $form->delete();

        return redirect()
            ->route('customer.lists.forms.index', $list)
            ->with('success', 'Subscription form deleted successfully.');
    }
}

