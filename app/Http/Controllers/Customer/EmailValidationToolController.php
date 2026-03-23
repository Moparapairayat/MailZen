<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\EmailValidationTool;
use Illuminate\Http\Request;

class EmailValidationToolController extends Controller
{
    private const META_ALLOWED_GROUP_IDS_KEY = 'allowed_customer_group_ids';

    public function __construct()
    {
        $this->middleware('customer.access:email_validation.permissions.can_create_tools')->only(['create', 'store']);
        $this->middleware('customer.access:email_validation.permissions.can_edit_tools')->only(['edit', 'update']);
        $this->middleware('customer.access:email_validation.permissions.can_delete_tools')->only(['destroy']);
    }

    protected function customerGroupIds($customer): array
    {
        if (!$customer) {
            return [];
        }

        $groups = $customer->relationLoaded('customerGroups')
            ? $customer->customerGroups
            : $customer->customerGroups()->get();

        return $groups
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }

    protected function globalToolAllowedForCustomer(EmailValidationTool $tool, $customer): bool
    {
        if ($tool->customer_id !== null) {
            return false;
        }

        $allowedGroupIds = (array) data_get($tool->meta ?? [], self::META_ALLOWED_GROUP_IDS_KEY, []);
        $allowedGroupIds = array_values(array_unique(array_filter(array_map('intval', $allowedGroupIds), fn ($id) => $id > 0)));

        if (empty($allowedGroupIds)) {
            return true;
        }

        $customerGroupIds = $this->customerGroupIds($customer);
        if (empty($customerGroupIds)) {
            return false;
        }

        return count(array_intersect($allowedGroupIds, $customerGroupIds)) > 0;
    }

    protected function authorizeManage(EmailValidationTool $tool): EmailValidationTool
    {
        $customerId = auth('customer')->id();

        if (!$customerId || (int) $tool->customer_id !== (int) $customerId) {
            abort(404);
        }

        return $tool;
    }

    protected function authorizeView(EmailValidationTool $tool): EmailValidationTool
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            abort(404);
        }

        if ((int) $tool->customer_id === (int) $customer->id) {
            return $tool;
        }

        $mustAddOwn = (bool) $customer->groupSetting('email_validation.must_add', false);

        if (!$mustAddOwn && $tool->customer_id === null && $this->globalToolAllowedForCustomer($tool, $customer)) {
            return $tool;
        }

        abort(404);
    }

    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $filters = $request->only(['search', 'active']);

        $mustAddOwn = (bool) $customer->groupSetting('email_validation.must_add', false);

        $customerGroupIds = $this->customerGroupIds($customer);

        $tools = EmailValidationTool::query()
            ->when($mustAddOwn, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) use ($customer) {
                $q->where(function ($sub) use ($customer) {
                    $sub->where('customer_id', $customer->id)
                        ->orWhere(function ($global) use ($customer) {
                            $global->whereNull('customer_id');
                        });
                });
            })
            ->when(!$mustAddOwn, function ($query) use ($customerGroupIds) {
                $query->where(function ($sub) use ($customerGroupIds) {
                    $sub->whereNotNull('customer_id')
                        ->orWhere(function ($global) use ($customerGroupIds) {
                            $global->whereNull('customer_id')
                                ->where(function ($scope) use ($customerGroupIds) {
                                    $scope->whereNull('meta')
                                        ->orWhereNull('meta->' . self::META_ALLOWED_GROUP_IDS_KEY)
                                        ->orWhereJsonLength('meta->' . self::META_ALLOWED_GROUP_IDS_KEY, 0);

                                    if (!empty($customerGroupIds)) {
                                        $scope->orWhere(function ($allowed) use ($customerGroupIds) {
                                            foreach ($customerGroupIds as $groupId) {
                                                $allowed->orWhereJsonContains('meta->' . self::META_ALLOWED_GROUP_IDS_KEY, (int) $groupId);
                                            }
                                        });
                                    }
                                });
                        });
                });
            })
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['active']) && $filters['active'] !== '', function ($query) use ($filters) {
                $query->where('active', (bool) $filters['active']);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('customer.email-validation.tools.index', compact('tools', 'filters'));
    }

    public function create()
    {
        return view('customer.email-validation.tools.create');
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'in:snapvalid'],
            'api_key' => ['required', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $currentCount = EmailValidationTool::where('customer_id', $customer->id)->count();
        $customer->enforceGroupLimit('email_validation.max_tools', $currentCount, 'You have reached the maximum number of email validation tools you can add.');

        $tool = EmailValidationTool::create([
            'customer_id' => $customer->id,
            'name' => $validated['name'],
            'provider' => $validated['provider'],
            'api_key' => $validated['api_key'],
            'active' => (bool) ($validated['active'] ?? true),
        ]);

        return redirect()
            ->route('customer.email-validation.tools.show', $tool)
            ->with('success', 'Email validation tool created.');
    }

    public function show(EmailValidationTool $tool)
    {
        $this->authorizeView($tool);

        return view('customer.email-validation.tools.show', compact('tool'));
    }

    public function edit(EmailValidationTool $tool)
    {
        $this->authorizeManage($tool);

        return view('customer.email-validation.tools.edit', compact('tool'));
    }

    public function update(Request $request, EmailValidationTool $tool)
    {
        $this->authorizeManage($tool);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'in:snapvalid'],
            'api_key' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

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

        return redirect()
            ->route('customer.email-validation.tools.show', $tool)
            ->with('success', 'Email validation tool updated.');
    }

    public function destroy(EmailValidationTool $tool)
    {
        $this->authorizeManage($tool);

        $tool->delete();

        return redirect()
            ->route('customer.email-validation.tools.index')
            ->with('success', 'Email validation tool deleted.');
    }
}
