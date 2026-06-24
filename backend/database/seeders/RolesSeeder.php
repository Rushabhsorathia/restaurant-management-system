<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'display_name' => 'Administrator', 'guard_name' => 'sanctum'],
            ['name' => 'manager', 'display_name' => 'Outlet Manager', 'guard_name' => 'sanctum'],
            ['name' => 'cashier', 'display_name' => 'Cashier', 'guard_name' => 'sanctum'],
            ['name' => 'waiter', 'display_name' => 'Waiter', 'guard_name' => 'sanctum'],
            ['name' => 'chef', 'display_name' => 'Chef', 'guard_name' => 'sanctum'],
            ['name' => 'captain', 'display_name' => 'Captain', 'guard_name' => 'sanctum'],
            ['name' => 'hq_admin', 'display_name' => 'HQ Admin', 'guard_name' => 'sanctum'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name'], 'guard_name' => $role['guard_name']],
                ['display_name' => $role['display_name']],
            );
        }
    }
}
