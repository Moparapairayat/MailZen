<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get paginated list of users.
     */
    public function getPaginated(array $filters = [], int $perPage = 15, string $pageName = 'page'): LengthAwarePaginator
    {
        $query = User::query()->with('userGroups');

        // Apply filters
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['user_group_id'])) {
            $query->whereHas('userGroups', function ($q) use ($filters) {
                $q->where('user_groups.id', $filters['user_group_id']);
            });
        }

        return $query->latest()->paginate($perPage, ['*'], $pageName);
    }

    /**
     * Create a new user.
     */
    public function create(array $data): User
    {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'timezone' => $data['timezone'] ?? 'UTC',
            'language' => $data['language'] ?? 'en',
            'status' => $data['status'] ?? 'active',
        ]);

        // Attach user groups
        if (isset($data['user_group_ids']) && is_array($data['user_group_ids'])) {
            $groupIds = array_values(array_unique(array_filter($data['user_group_ids'], function ($id) {
                return !is_null($id) && $id !== '';
            })));
            $user->userGroups()->sync($groupIds);
        }

        return $user->load('userGroups');
    }

    /**
     * Update an existing user.
     */
    public function update(User $user, array $data): User
    {
        $updateData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'timezone' => $data['timezone'] ?? $user->timezone,
            'language' => $data['language'] ?? $user->language,
            'status' => $data['status'] ?? $user->status,
        ];

        // Update password if provided
        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        // Sync user groups
        if (isset($data['user_group_ids']) && is_array($data['user_group_ids'])) {
            $groupIds = array_values(array_unique(array_filter($data['user_group_ids'], function ($id) {
                return !is_null($id) && $id !== '';
            })));
            $user->userGroups()->sync($groupIds);
        }

        return $user->load('userGroups');
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        return (bool) DB::transaction(function () use ($user) {
            $user->userGroups()->detach();
            $user->forceFill([
                'email' => $this->deletedEmail((string) $user->email, (int) $user->id),
            ])->save();

            return $user->delete();
        });
    }

    private function deletedEmail(string $email, int $id): string
    {
        $timestamp = time();

        return "deleted+{$id}+{$timestamp}@mailzen.invalid";
    }

    /**
     * Get all user groups for select options.
     */
    public function getUserGroupsForSelect(): array
    {
        $systemGroups = [
            [
                'name' => 'Admin',
                'attributes' => [
                    'description' => 'Default admin access (configurable via Accessibility Control).',
                    'permissions' => ['admin.*'],
                    'is_system' => true,
                ],
            ],
            [
                'name' => 'Superadmin',
                'attributes' => [
                    'description' => 'Full access to all admin actions.',
                    'permissions' => ['*'],
                    'is_system' => true,
                ],
            ],
        ];

        foreach ($systemGroups as $groupData) {
            $name = (string) $groupData['name'];
            $attributes = (array) $groupData['attributes'];

            $group = UserGroup::withTrashed()
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->first();

            if (!$group) {
                UserGroup::create(array_merge(['name' => $name], $attributes));
                continue;
            }

            if ($group->trashed()) {
                $group->restore();
            }
        }

        return UserGroup::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
