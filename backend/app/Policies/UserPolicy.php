<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('users.view');
    }

    public function view(User $actor, User $user): bool
    {
        return $actor->can('users.view');
    }

    public function create(User $actor): bool
    {
        return $actor->can('users.create');
    }

    public function update(User $actor, User $user): bool
    {
        if (! $actor->can('users.update')) {
            return false;
        }
        // Users can always update themselves (e.g., profile).
        if ($actor->id === $user->id) {
            return true;
        }

        // Cross-restaurant isolation: managers cannot edit users from
        // another restaurant.
        return $this->sameTenant($actor, $user);
    }

    public function delete(User $actor, User $user): bool
    {
        return $actor->can('users.delete') && $this->sameTenant($actor, $user);
    }

    public function toggleStatus(User $actor, User $user): bool
    {
        return $actor->can('users.update') && $this->sameTenant($actor, $user);
    }

    public function resetPassword(User $actor, User $user): bool
    {
        return $actor->can('users.update') && $this->sameTenant($actor, $user);
    }

    public function manageRoles(User $actor): bool
    {
        return $actor->can('role.update');
    }

    protected function sameTenant(User $actor, User $user): bool
    {
        if ($actor->hasRole('hq_admin')) {
            return true;
        }

        return $actor->restaurant_id !== null
            && $actor->restaurant_id === $user->restaurant_id;
    }
}
