<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Outlet;
use App\Models\User;

class OutletPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('outlets.view');
    }

    public function view(User $actor, Outlet $outlet): bool
    {
        if (! $actor->can('outlets.view')) {
            return false;
        }

        return $this->sameTenant($actor, $outlet);
    }

    public function create(User $actor): bool
    {
        return $actor->can('outlets.create');
    }

    public function update(User $actor, Outlet $outlet): bool
    {
        return $actor->can('outlets.update') && $this->sameTenant($actor, $outlet);
    }

    public function delete(User $actor, Outlet $outlet): bool
    {
        return $actor->can('outlets.delete') && $this->sameTenant($actor, $outlet);
    }

    protected function sameTenant(User $actor, Outlet $outlet): bool
    {
        if ($actor->hasRole('hq_admin')) {
            return true;
        }

        return $actor->restaurant_id !== null
            && $actor->restaurant_id === $outlet->restaurant_id;
    }
}