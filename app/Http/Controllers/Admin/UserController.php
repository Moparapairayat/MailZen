<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Services\CustomerService;
use App\Models\User;
use App\Services\UserService;
use DateTimeZone;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected CustomerService $customerService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'status', 'user_group_id']);
        $users = $this->userService->getPaginated($filters, 15, 'users_page');
        $users->appends($request->except('users_page'));
        $userGroups = $this->userService->getUserGroupsForSelect();

        $customersFilters = $request->only(['search', 'status', 'customer_group_id']);
        $customers = $this->customerService->getPaginated($customersFilters, 15, 'customers_page');
        $customers->appends($request->except('customers_page'));

        return view('admin.users.index', compact('users', 'userGroups', 'filters', 'customers', 'customersFilters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $userGroups = $this->userService->getUserGroupsForSelect();
        $timezones = DateTimeZone::listIdentifiers();
        return view('admin.users.create', compact('userGroups', 'timezones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $user = $this->userService->create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('userGroups');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load('userGroups');
        $userGroups = $this->userService->getUserGroupsForSelect();
        $timezones = DateTimeZone::listIdentifiers();
        return view('admin.users.edit', compact('user', 'userGroups', 'timezones'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $this->userService->update($user, $request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->userService->delete($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}

