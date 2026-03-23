<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use App\Models\EmailValidationRun;
use App\Models\EmailValidationTool;
use Illuminate\Http\Request;

class EmailValidationController extends Controller
{
    private const META_ALLOWED_GROUP_IDS_KEY = 'allowed_customer_group_ids';

    public function index(Request $request)
    {
        $toolSearch = is_string($request->query('tool_search')) ? trim($request->query('tool_search')) : null;
        $runSearch = is_string($request->query('run_search')) ? trim($request->query('run_search')) : null;
        $runStatus = is_string($request->query('run_status')) ? trim($request->query('run_status')) : null;

        $tools = EmailValidationTool::query()
            ->with(['customer'])
            ->when($toolSearch, function ($q) use ($toolSearch) {
                $q->where(function ($sub) use ($toolSearch) {
                    $sub->where('name', 'like', "%{$toolSearch}%")
                        ->orWhere('provider', 'like', "%{$toolSearch}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($toolSearch) {
                            $customerQuery->where('email', 'like', "%{$toolSearch}%")
                                ->orWhere('first_name', 'like', "%{$toolSearch}%")
                                ->orWhere('last_name', 'like', "%{$toolSearch}%");
                        });
                });
            })
            ->latest()
            ->paginate(15, ['*'], 'tools_page')
            ->withQueryString();

        $allAllowedGroupIds = [];
        foreach ($tools->items() as $tool) {
            if ($tool->customer_id !== null) {
                continue;
            }

            $ids = (array) data_get($tool->meta ?? [], self::META_ALLOWED_GROUP_IDS_KEY, []);
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn ($id) => $id > 0)));
            if (!empty($ids)) {
                $allAllowedGroupIds = array_merge($allAllowedGroupIds, $ids);
            }
        }

        $allAllowedGroupIds = array_values(array_unique($allAllowedGroupIds));

        $customerGroupNamesById = empty($allAllowedGroupIds)
            ? []
            : CustomerGroup::query()
                ->whereIn('id', $allAllowedGroupIds)
                ->pluck('name', 'id')
                ->mapWithKeys(fn ($name, $id) => [(int) $id => $name])
                ->all();

        $runs = EmailValidationRun::query()
            ->with(['customer', 'list', 'tool'])
            ->when($runStatus && $runStatus !== 'all', function ($q) use ($runStatus) {
                $q->where('status', $runStatus);
            })
            ->when($runSearch, function ($q) use ($runSearch) {
                $q->where(function ($sub) use ($runSearch) {
                    $sub->where('id', $runSearch)
                        ->orWhereHas('customer', function ($customerQuery) use ($runSearch) {
                            $customerQuery->where('email', 'like', "%{$runSearch}%")
                                ->orWhere('first_name', 'like', "%{$runSearch}%")
                                ->orWhere('last_name', 'like', "%{$runSearch}%");
                        })
                        ->orWhereHas('list', function ($listQuery) use ($runSearch) {
                            $listQuery->where('name', 'like', "%{$runSearch}%");
                        })
                        ->orWhereHas('tool', function ($toolQuery) use ($runSearch) {
                            $toolQuery->where('name', 'like', "%{$runSearch}%")
                                ->orWhere('provider', 'like', "%{$runSearch}%");
                        });
                });
            })
            ->latest()
            ->paginate(15, ['*'], 'runs_page')
            ->withQueryString();

        return view('admin.email-validation.index', compact('tools', 'runs', 'toolSearch', 'runSearch', 'runStatus', 'customerGroupNamesById'));
    }
}
