<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail, CanResetPasswordContract
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, MustVerifyEmailTrait, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'timezone',
        'language',
        'status',
        'avatar_path',
        'bio',
        'website_url',
        'twitter_url',
        'facebook_url',
        'linkedin_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'admin_permissions' => 'array',
            'admin_permissions_override' => 'boolean',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the user groups that belong to the user.
     */
    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_user_group')
            ->withTimestamps();
    }

    /**
     * Check if user belongs to a specific group.
     */
    public function hasUserGroup(string $groupName): bool
    {
        return $this->userGroups()->where('name', $groupName)->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->userGroups()
            ->whereJsonContains('permissions', $permission)
            ->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->userGroups()
            ->whereRaw('LOWER(name) = ?', ['superadmin'])
            ->orWhereRaw('LOWER(name) = ?', ['super admin'])
            ->exists();
    }

    public function hasAdminAbility(string $ability): bool
    {
        if (($this->admin_permissions_override ?? false) === true) {
            $permissions = (array) ($this->admin_permissions ?? []);
            foreach ($permissions as $perm) {
                if (!is_string($perm) || $perm === '') {
                    continue;
                }

                if (Str::is($perm, $ability)) {
                    return true;
                }
            }

            return false;
        }

        $groups = $this->relationLoaded('userGroups')
            ? $this->userGroups
            : $this->userGroups()->get();

        foreach ($groups as $group) {
            $permissions = (array) ($group->permissions ?? []);
            foreach ($permissions as $perm) {
                if (!is_string($perm) || $perm === '') {
                    continue;
                }

                if (Str::is($perm, $ability)) {
                    return true;
                }
            }
        }

        return false;
    }
}

