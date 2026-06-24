<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\ApiResetPasswordNotification;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->seed(PermissionsSeeder::class);
    }

    /**
     * @return array{admin: User, restaurant: Restaurant, outlets: array<int, Outlet>, cashier: User}
     */
    protected function seedContext(): array
    {
        $restaurant = Restaurant::factory()->create();
        $outlets = Outlet::factory()->count(2)->create(['restaurant_id' => $restaurant->id])->all();

        $admin = User::factory()->create([
            'restaurant_id' => $restaurant->id,
            'email' => 'admin@x.io',
            'is_active' => true,
        ]);
        $admin->outlets()->attach($outlets[0]->id);
        $admin->assignRole('admin');

        $cashier = User::factory()->create([
            'restaurant_id' => $restaurant->id,
            'email' => 'cashier@x.io',
            'is_active' => true,
        ]);
        $cashier->outlets()->attach($outlets[0]->id);
        $cashier->assignRole('cashier');

        return [
            'admin' => $admin,
            'restaurant' => $restaurant,
            'outlets' => $outlets,
            'cashier' => $cashier,
        ];
    }

    protected function authHeaders(User $user): array
    {
        return [
            'Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken,
            'Accept' => 'application/json',
        ];
    }

    public function test_admin_can_list_users(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'roles', 'outlets', 'is_active'],
                ],
                'links', 'meta',
            ]);
    }

    public function test_cashier_cannot_access_users_endpoints(): void
    {
        ['cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->authHeaders($cashier))
            ->getJson('/api/v1/users')
            ->assertStatus(403);
    }

    public function test_admin_can_create_user_with_roles_and_outlets(): void
    {
        Notification::fake();
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();

        $response = $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/v1/users', [
                'name' => 'New Waiter',
                'email' => 'waiter@x.io',
                'phone' => '+91-9999999999',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'roles' => ['waiter'],
                'outlets' => [$outlets[0]->id, $outlets[1]->id],
                'is_active' => true,
                'must_change_password' => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.email', 'waiter@x.io');
        $response->assertJsonPath('data.roles.0', 'waiter');

        $created = User::where('email', 'waiter@x.io')->firstOrFail();
        $this->assertTrue($created->hasRole('waiter'));
        $this->assertSame(2, $created->outlets()->count());
    }

    public function test_creating_user_with_duplicate_email_returns_422(): void
    {
        ['admin' => $admin, 'cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/v1/users', [
                'name' => 'Dup',
                'email' => $cashier->email,
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'roles' => ['cashier'],
                'outlets' => [$cashier->outlets()->first()->id],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_creating_user_without_roles_or_outlets_returns_422(): void
    {
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->postJson('/api/v1/users', [
                'name' => 'Missing',
                'email' => 'missing@x.io',
                'password' => 'Password1!',
                'password_confirmation' => 'Password1!',
                'roles' => [],
                'outlets' => [],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['roles', 'outlets']);
    }

    public function test_admin_can_update_user_syncing_roles_and_outlets(): void
    {
        Notification::fake();
        ['admin' => $admin, 'cashier' => $cashier, 'outlets' => $outlets] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->putJson("/api/v1/users/{$cashier->id}", [
                'name' => 'Cashier Updated',
                'email' => $cashier->email,
                'roles' => ['manager', 'cashier'],
                'outlets' => [$outlets[1]->id],
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Cashier Updated');

        $cashier->refresh();
        $this->assertTrue($cashier->hasRole('manager'));
        $this->assertTrue($cashier->hasRole('cashier'));
        $this->assertSame(1, $cashier->outlets()->count());
        $this->assertSame($outlets[1]->id, $cashier->outlets()->first()->id);
    }

    public function test_deactivating_user_blocks_login(): void
    {
        ['admin' => $admin, 'cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->patchJson("/api/v1/users/{$cashier->id}/status", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->postJson('/api/v1/auth/login', [
            'email' => $cashier->email,
            'password' => 'password',
        ])->assertStatus(403);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->patchJson("/api/v1/users/{$admin->id}/status", ['is_active' => false])
            ->assertStatus(422);
    }

    public function test_admin_can_soft_delete_user(): void
    {
        ['admin' => $admin, 'cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->deleteJson("/api/v1/users/{$cashier->id}")
            ->assertOk();

        $this->assertNull(User::find($cashier->id));
        $this->assertNotNull(User::withTrashed()->find($cashier->id));
    }

    public function test_admin_cannot_delete_self(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->deleteJson("/api/v1/users/{$admin->id}")
            ->assertStatus(422);
    }

    public function test_search_filter_finds_user(): void
    {
        ['admin' => $admin] = $this->seedContext();
        User::factory()->create([
            'restaurant_id' => $admin->restaurant_id,
            'name' => 'Findme Singh',
            'email' => 'findme@x.io',
        ])->outlets()->attach($admin->outlets()->first()->id);

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/users?search=findme')
            ->assertOk()
            ->assertJsonPath('data.0.email', 'findme@x.io');
    }

    public function test_role_and_outlet_filters_work(): void
    {
        ['admin' => $admin, 'cashier' => $cashier, 'outlets' => $outlets] = $this->seedContext();

        $other = User::factory()->create([
            'restaurant_id' => $admin->restaurant_id,
            'email' => 'manager@x.io',
        ]);
        $other->outlets()->attach($outlets[1]->id);
        $other->assignRole('manager');

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/users?role=manager')
            ->assertOk()
            ->assertJsonPath('data.0.email', 'manager@x.io');

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/users?outlet_id='.$outlets[1]->id)
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_reset_user_password_via_email(): void
    {
        Notification::fake();
        ['admin' => $admin, 'cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->postJson("/api/v1/users/{$cashier->id}/reset-password")
            ->assertOk();

        Notification::assertSentTo(
            $cashier,
            ApiResetPasswordNotification::class
        );
    }

    public function test_admin_can_list_roles(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'display_name', 'guard_name', 'permissions_count'],
                ],
            ]);
    }

    public function test_admin_can_get_role_permissions(): void
    {
        ['admin' => $admin] = $this->seedContext();
        $role = Role::where('name', 'cashier')->firstOrFail();

        $this->withHeaders($this->authHeaders($admin))
            ->getJson("/api/v1/roles/{$role->id}/permissions")
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'role' => ['id', 'name', 'permissions'],
                'permissions',
            ]);
    }

    public function test_admin_can_update_role_permissions(): void
    {
        ['admin' => $admin] = $this->seedContext();
        $role = Role::where('name', 'cashier')->firstOrFail();

        $this->withHeaders($this->authHeaders($admin))
            ->putJson("/api/v1/roles/{$role->id}/permissions", [
                'permissions' => ['menu.view', 'order.view'],
            ])
            ->assertOk();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('menu.view'));
        $this->assertTrue($role->hasPermissionTo('order.view'));
        $this->assertFalse($role->hasPermissionTo('bill.settle'));
    }

    public function test_permissions_endpoint_returns_grouped_modules(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->authHeaders($admin))
            ->getJson('/api/v1/permissions')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'modules' => [
                    '*' => ['module', 'permissions'],
                ],
            ]);
    }
}
