<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestaurantSetupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
        $this->seed(PermissionsSeeder::class);
    }

    /**
     * @return array{admin: User, cashier: User, restaurant: Restaurant, outlets: array<int, Outlet>}
     */
    protected function seedContext(): array
    {
        $restaurant = Restaurant::factory()->create([
            'state' => 'Gujarat',
            'gstin' => '24ABCDE1234F1Z5',
            'pan' => 'ABCDE1234F',
        ]);

        $outlets = [
            Outlet::factory()->create(['restaurant_id' => $restaurant->id, 'code' => 'MAIN', 'state' => 'Gujarat']),
            Outlet::factory()->create(['restaurant_id' => $restaurant->id, 'code' => 'BR2', 'state' => 'Maharashtra']),
        ];

        $admin = User::factory()->create([
            'restaurant_id' => $restaurant->id,
            'email' => 'admin@x.io',
        ]);
        $admin->outlets()->attach([$outlets[0]->id, $outlets[1]->id]);
        $admin->assignRole('admin');

        $cashier = User::factory()->create([
            'restaurant_id' => $restaurant->id,
            'email' => 'cashier@x.io',
        ]);
        $cashier->outlets()->attach($outlets[0]->id);
        $cashier->assignRole('cashier');

        return [
            'admin' => $admin,
            'cashier' => $cashier,
            'restaurant' => $restaurant,
            'outlets' => $outlets,
        ];
    }

    protected function headers(User $user, array $extra = []): array
    {
        return array_merge([
            'Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken,
            'Accept' => 'application/json',
        ], $extra);
    }

    public function test_admin_can_view_restaurant_profile(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->getJson('/api/v1/restaurant')
            ->assertOk()
            ->assertJsonPath('restaurant.name', $admin->restaurant->name);
    }

    public function test_admin_can_update_restaurant_profile(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->putJson('/api/v1/restaurant', [
                'name' => 'New Restaurant Name',
                'legal_name' => 'New Legal Pvt Ltd',
                'email' => 'new@rms.local',
                'phone' => '+91-9999999999',
                'address_line1' => '1 New Street',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395007',
                'country' => 'India',
                'currency_code' => 'INR',
                'timezone' => 'Asia/Kolkata',
            ])
            ->assertOk()
            ->assertJsonPath('restaurant.name', 'New Restaurant Name')
            ->assertJsonPath('restaurant.city', 'Surat');
    }

    public function test_restaurant_update_rejects_invalid_gstin(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->putJson('/api/v1/restaurant', [
                'gstin' => 'INVALIDGSTIN',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['gstin']);
    }

    public function test_restaurant_update_rejects_invalid_pan(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->putJson('/api/v1/restaurant', [
                'pan' => '12345',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['pan']);
    }

    public function test_admin_can_upload_logo(): void
    {
        Storage::fake('public');
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/restaurant/logo', [
                'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
            ])
            ->assertOk()
            ->assertJsonStructure(['success', 'logo_url']);

        $this->assertNotNull($admin->restaurant->fresh()->logo_url);
    }

    public function test_logo_upload_rejects_oversized_file(): void
    {
        Storage::fake('public');
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/restaurant/logo', [
                'logo' => UploadedFile::fake()->create('big.pdf', 3000, 'application/pdf'),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['logo']);
    }

    public function test_cashier_cannot_update_restaurant(): void
    {
        ['cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->headers($cashier))
            ->putJson('/api/v1/restaurant', ['name' => 'Hack'])
            ->assertStatus(403);
    }

    public function test_admin_can_list_outlets(): void
    {
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();

        $response = $this->withHeaders($this->headers($admin))
            ->getJson('/api/v1/outlets')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $codes = collect($response->json('data'))->pluck('code');
        $this->assertContains($outlets[0]->code, $codes);
        $this->assertContains($outlets[1]->code, $codes);
    }

    public function test_admin_can_create_outlet(): void
    {
        ['admin' => $admin] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/outlets', [
                'name' => 'Third Branch',
                'code' => 'BR3',
                'type' => 'qsr',
                'address_line1' => '20 Market Road',
                'city' => 'Vadodara',
                'state' => 'Gujarat',
                'pincode' => '390001',
                'is_active' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('data.code', 'BR3');

        $this->assertDatabaseHas('outlets', ['code' => 'BR3']);
    }

    public function test_creating_outlet_with_duplicate_code_returns_422(): void
    {
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/outlets', [
                'name' => 'Dup',
                'code' => $outlets[0]->code,
                'type' => 'qsr',
                'address_line1' => 'x',
                'city' => 'x',
                'state' => 'x',
                'pincode' => '111111',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_admin_can_update_outlet(): void
    {
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/outlets/{$outlets[1]->id}", [
                'name' => 'Renamed',
                'address_line1' => 'x',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411001',
            ])
            ->assertOk()
            ->assertJsonPath('outlet.name', 'Renamed')
            ->assertJsonPath('outlet.city', 'Pune');
    }

    public function test_admin_can_toggle_outlet_status(): void
    {
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();

        $this->withHeaders($this->headers($admin))
            ->patchJson("/api/v1/outlets/{$outlets[1]->id}/status", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertFalse((bool) $outlets[1]->fresh()->is_active);
    }

    public function test_outlet_index_excludes_inactive_when_active_only(): void
    {
        ['admin' => $admin, 'outlets' => $outlets] = $this->seedContext();
        $outlets[1]->update(['is_active' => false]);

        $this->withHeaders($this->headers($admin))
            ->getJson('/api/v1/outlets?active_only=1')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_cashier_cannot_create_outlet(): void
    {
        ['cashier' => $cashier] = $this->seedContext();

        $this->withHeaders($this->headers($cashier))
            ->postJson('/api/v1/outlets', [
                'name' => 'x',
                'code' => 'X',
                'type' => 'qsr',
                'address_line1' => 'x',
                'city' => 'x',
                'state' => 'x',
                'pincode' => '1',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_manage_tax_configs(): void
    {
        ['admin' => $admin, 'restaurant' => $restaurant] = $this->seedContext();

        $create = $this->withHeaders($this->headers($admin))
            ->postJson('/api/v1/tax-configs', [
                'name' => 'GST 18%',
                'type' => 'intra_state',
                'cgst_rate' => 9,
                'sgst_rate' => 9,
                'igst_rate' => 18,
                'cess_rate' => 0,
                'is_active' => true,
            ]);
        $create->assertStatus(201);
        $configId = $create->json('data.id');

        $update = $this->withHeaders($this->headers($admin))
            ->putJson("/api/v1/tax-configs/{$configId}", ['cgst_rate' => 10])
            ->assertOk();
        $this->assertEquals(10.0, (float) $update->json('tax_config.cgst_rate'));
        $this->assertEquals(10.0, (float) $this->withHeaders($this->headers($admin))->getJson('/api/v1/tax-configs')->json('data.0.cgst_rate'));

        $this->withHeaders($this->headers($admin))
            ->getJson('/api/v1/tax-configs')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withHeaders($this->headers($admin))
            ->deleteJson("/api/v1/tax-configs/{$configId}")
            ->assertOk();

        $this->assertDatabaseMissing('tax_configs', ['id' => $configId]);
    }

    public function test_tax_service_resolves_intra_and_inter_state(): void
    {
        ['restaurant' => $restaurant] = $this->seedContext();

        \App\Models\TaxConfig::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'CGST+SGST 5%',
            'type' => 'intra_state',
            'cgst_rate' => 2.5,
            'sgst_rate' => 2.5,
            'igst_rate' => 0,
            'cess_rate' => 0,
            'is_active' => true,
        ]);
        \App\Models\TaxConfig::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'IGST 5%',
            'type' => 'inter_state',
            'cgst_rate' => 0,
            'sgst_rate' => 0,
            'igst_rate' => 5,
            'cess_rate' => 0,
            'is_active' => true,
        ]);

        $service = app(\App\Services\TaxService::class);

        $intra = $service->resolve($restaurant, 'gujarat');
        $this->assertNotNull($intra);
        $this->assertSame('intra_state', $intra->type);

        $inter = $service->resolve($restaurant, 'maharashtra');
        $this->assertNotNull($inter);
        $this->assertSame('inter_state', $inter->type);

        $split = $service->split($intra, 1000.0);
        $this->assertSame(25.0, $split['cgst']);
        $this->assertSame(25.0, $split['sgst']);
        $this->assertSame(0.0, $split['igst']);
    }

    public function test_me_outlets_returns_active_assigned_outlets(): void
    {
        ['cashier' => $cashier, 'outlets' => $outlets] = $this->seedContext();
        $outlets[1]->update(['is_active' => false]);

        $this->withHeaders($this->headers($cashier))
            ->getJson('/api/v1/me/outlets')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', $outlets[0]->code);
    }

    public function test_tenant_scope_rejects_outlet_outside_restaurant(): void
    {
        ['admin' => $admin] = $this->seedContext();
        $otherRestaurant = Restaurant::factory()->create();
        $otherOutlet = Outlet::factory()->create(['restaurant_id' => $otherRestaurant->id]);

        $this->withHeaders($this->headers($admin, ['X-Outlet-Id' => $otherOutlet->id]))
            ->getJson('/api/v1/me/outlets')
            ->assertStatus(403);
    }

    public function test_tenant_scope_rejects_unassigned_outlet(): void
    {
        ['cashier' => $cashier, 'outlets' => $outlets] = $this->seedContext();

        $this->withHeaders($this->headers($cashier, ['X-Outlet-Id' => $outlets[1]->id]))
            ->getJson('/api/v1/me/outlets')
            ->assertStatus(403);
    }
}