<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('role.view');
    }

    public function view(User $actor, Role $role): bool
    {
        return $actor->can('role.view');
    }

    public function update(User $actor, Role $role): bool
    {
        // System-managed roles cannot be edited.
        if (in_array($role->name, ['admin'], true) && ! $actor->hasRole('hq_admin')) {
            return false;
        }

        return $actor->can('role.update');
    }
}
