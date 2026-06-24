<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Permission matrix keyed by role => [permission names].
     *
     * Modules: menu, order, bill, kot, payment, table, customer, inventory,
     *          supplier, purchase, report, settings, user, role, outlet, restaurant
     */
    public function run(): void
    {
        $modules = [
            'menu', 'order', 'bill', 'kot', 'payment',
            'table', 'customer', 'inventory', 'supplier',
            'purchase', 'report', 'settings', 'user',
            'role', 'outlet', 'restaurant',
        ];

        $actions = ['view', 'create', 'update', 'delete'];

        $allPermissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $allPermissions[] = [
                    'name' => "{$module}.{$action}",
                    'display_name' => ucfirst($action).' '.ucfirst($module),
                    'module' => $module,
                    'guard_name' => 'sanctum',
                ];
            }
        }

        // Special / workflow actions referenced by later tickets.
        $extras = [
            ['name' => 'bill.settle', 'display_name' => 'Settle Bill', 'module' => 'bill', 'guard_name' => 'sanctum'],
            ['name' => 'bill.refund', 'display_name' => 'Refund Bill', 'module' => 'bill', 'guard_name' => 'sanctum'],
            ['name' => 'kot.print', 'display_name' => 'Print KOT', 'module' => 'kot', 'guard_name' => 'sanctum'],
            ['name' => 'kot.void', 'display_name' => 'Void KOT', 'module' => 'kot', 'guard_name' => 'sanctum'],
            ['name' => 'order.hold', 'display_name' => 'Hold Order', 'module' => 'order', 'guard_name' => 'sanctum'],
            ['name' => 'order.cancel', 'display_name' => 'Cancel Order', 'module' => 'order', 'guard_name' => 'sanctum'],
            ['name' => 'order.transfer', 'display_name' => 'Transfer Order', 'module' => 'order', 'guard_name' => 'sanctum'],
        ];
        $allPermissions = array_merge($allPermissions, $extras);

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => $perm['guard_name']],
                ['display_name' => $perm['display_name'], 'module' => $perm['module']],
            );
        }

        $matrix = [
            'admin' => array_column($allPermissions, 'name'),
            'hq_admin' => array_column($allPermissions, 'name'),
            'manager' => collect($modules)
                ->flatMap(fn (string $m): array => array_map(
                    fn (string $a): string => "{$m}.{$a}",
                    $actions
                ))
                ->values()
                ->all(),
            'cashier' => [
                'menu.view',
                'order.view', 'order.create', 'order.update', 'order.hold', 'order.cancel',
                'bill.view', 'bill.create', 'bill.update', 'bill.settle', 'bill.refund',
                'payment.view', 'payment.create',
                'kot.view', 'kot.create', 'kot.print',
                'customer.view', 'customer.create',
                'table.view', 'table.update',
                'report.view',
            ],
            'waiter' => [
                'menu.view',
                'order.view', 'order.create', 'order.update',
                'kot.view',
                'table.view', 'table.update',
                'customer.view', 'customer.create',
            ],
            'chef' => [
                'menu.view',
                'order.view',
                'kot.view', 'kot.update',
                'inventory.view',
            ],
            'captain' => [
                'menu.view',
                'order.view', 'order.create', 'order.update',
                'kot.view',
                'table.view', 'table.update',
                'customer.view', 'customer.create',
            ],
        ];

        foreach ($matrix as $roleName => $perms) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions($perms);
            }
        }
    }
}
