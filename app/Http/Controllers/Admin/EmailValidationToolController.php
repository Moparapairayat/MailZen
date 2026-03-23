<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\EmailValidationTool;
use Illuminate\Http\Request;

class EmailValidationToolController extends Controller
{
    private const META_ALLOWED_GROUP_IDS_KEY = 'allowed_customer_group_ids';

    protected function authorizeGlobalTool(EmailValidationTool $tool): EmailValidationTool
    {
        if ($tool->customer_id !== null) {
            abort(404);
        }

        return $tool;
    }

    protected function sanitizeGroupIds(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $ids = [];
        foreach ($value as $item) {
            if (is_numeric($item)) {
                $ids[] = (int) $item;
            }
        }

        $ids = array_values(array_unique(array_filter($ids, fn ($id) => $id > 0)));

        if (empty($ids)) {
            return [];
        }

        return CustomerGroup::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    protected function applyAllowedGroupsMeta(EmailValidationTool $tool, array $groupIds): void
    {
        $meta = (array) ($tool->meta ?? []);

        if (empty($groupIds)) {
            unset($meta[self::META_ALLOWED_GROUP_IDS_KEY]);
        } else {
            $meta[self::META_ALLOWED_GROUP_IDS_KEY] = array_values($groupIds);
        }

        $tool->meta = empty($meta) ? null : $meta;
    }

    public function create()
    {
        $customerGroups = CustomerGroup::query()->orderBy('name')->get();

        return view('admin.email-validation.tools.create', compact('customerGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'in:snapvalid'],
            'api_key' => ['required', 'string'],
            'active' => ['nullable', 'boolean'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', 'exists:customer_groups,id'],
        ]);

        $groupIds = $this->sanitizeGroupIds($validated['customer_group_ids'] ?? []);

        $tool = EmailValidationTool::create([
            'customer_id' => null,
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'api_key' => $validated['api_key'],
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        $this->applyAllowedGroupsMeta($tool, $groupIds);
        $tool->save();

        return redirect()
            ->route('admin.email-validation.index')
            ->with('success', 'Email validation tool created.');
    }

    public function edit(EmailValidationTool $tool)
    {
        $this->authorizeGlobalTool($tool);

        $customerGroups = CustomerGroup::query()->orderBy('name')->get();

        $selectedGroupIds = (array) data_get($tool->meta ?? [], self::META_ALLOWED_GROUP_IDS_KEY, []);
        $selectedGroupIds = array_values(array_unique(array_filter(array_map('intval', $selectedGroupIds), fn ($id) => $id > 0)));

        return view('admin.email-validation.tools.edit', compact('tool', 'customerGroups', 'selectedGroupIds'));
    }

    public function update(Request $request, EmailValidationTool $tool)
    {
        $this->authorizeGlobalTool($tool);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'in:snapvalid'],
            'api_key' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', 'exists:customer_groups,id'],
        ]);

        $groupIds = $this->sanitizeGroupIds($validated['customer_group_ids'] ?? []);

        if (!array_key_exists('api_key', $validated) || !is_string($validated['api_key']) || trim($validated['api_key']) === '') {
            unset($validated['api_key']);
        }

        $tool->update([
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'active' => (bool) ($validated['active'] ?? false),
        ]);

        if (isset($validated['api_key'])) {
            $tool->api_key = $validated['api_key'];
            $tool->save();
        }

        $this->applyAllowedGroupsMeta($tool, $groupIds);
        $tool->save();

        return redirect()
            ->route('admin.email-validation.index')
            ->with('success', 'Email validation tool updated.');
    }

    public function destroy(EmailValidationTool $tool)
    {
        $this->authorizeGlobalTool($tool);

        $tool->delete();

        return redirect()
            ->route('admin.email-validation.index')
            ->with('success', 'Email validation tool deleted.');
    }
}
