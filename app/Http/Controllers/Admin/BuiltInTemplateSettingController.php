<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BuiltInTemplateSetting;
use App\Models\CustomerGroup;
use Illuminate\Http\Request;

class BuiltInTemplateSettingController extends Controller
{
    public function edit(BuiltInTemplateSetting $builtInTemplateSetting)
    {
        return view('admin.built-in-templates.edit', [
            'setting' => $builtInTemplateSetting->loadMissing('customerGroups'),
            'customerGroups' => CustomerGroup::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, BuiltInTemplateSetting $builtInTemplateSetting)
    {
        $data = $request->validate([
            'is_active' => ['boolean'],
            'customer_group_ids' => ['nullable', 'array'],
            'customer_group_ids.*' => ['integer', 'exists:customer_groups,id'],
        ]);

        $builtInTemplateSetting->update([
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $groupIds = $data['customer_group_ids'] ?? null;
        if ($groupIds === null) {
            return redirect()
                ->route('admin.built-in-templates.edit', $builtInTemplateSetting)
                ->with('success', __('Template updated.'));
        }

        $ids = [];
        if (is_array($groupIds)) {
            $ids = array_values(array_unique(array_map(fn ($v) => (int) $v, $groupIds)));
        }

        if (empty($ids)) {
            $builtInTemplateSetting->customerGroups()->detach();
        } else {
            $builtInTemplateSetting->customerGroups()->sync($ids);
        }

        return redirect()
            ->route('admin.built-in-templates.edit', $builtInTemplateSetting)
            ->with('success', __('Template updated.'));
    }
}
