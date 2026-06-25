<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TaxConfig;
use App\Models\User;

class TaxConfigPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('tax.view');
    }

    public function view(User $actor, TaxConfig $config): bool
    {
        return $actor->can('tax.view') && $this->sameTenant($actor, $config);
    }

    public function create(User $actor): bool
    {
        return $actor->can('tax.update');
    }

    public function update(User $actor, TaxConfig $config): bool
    {
        return $actor->can('tax.update') && $this->sameTenant($actor, $config);
    }

    public function delete(User $actor, TaxConfig $config): bool
    {
        return $actor->can('tax.delete') && $this->sameTenant($actor, $config);
    }

    protected function sameTenant(User $actor, TaxConfig $config): bool
    {
        if ($actor->hasRole('hq_admin')) {
            return true;
        }

        return $actor->restaurant_id !== null
            && $actor->restaurant_id === $config->restaurant_id;
    }
}