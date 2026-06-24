<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\TaxConfig;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_tables_exist(): void
    {
        $expected = [
            'users', 'restaurants', 'outlets', 'tax_configs', 'discount_configs',
            'app_settings', 'personal_access_tokens', 'user_outlets',
            'roles', 'permissions', 'model_has_roles', 'model_has_permissions',
            'role_has_permissions', 'units', 'languages',
        ];

        foreach ($expected as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "Expected table {$table} to exist"
            );
        }
    }

    public function test_critical_columns_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('users', 'restaurant_id'));
        $this->assertTrue(Schema::hasColumn('users', 'phone'));
        $this->assertTrue(Schema::hasColumn('users', 'is_active'));
        $this->assertTrue(Schema::hasColumn('users', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('restaurants', 'gstin'));
        $this->assertTrue(Schema::hasColumn('outlets', 'code'));
        $this->assertTrue(Schema::hasColumn('tax_configs', 'cgst_rate'));
        $this->assertTrue(Schema::hasColumn('permissions', 'display_name'));
        $this->assertTrue(Schema::hasColumn('permissions', 'module'));
        $this->assertTrue(Schema::hasColumn('roles', 'display_name'));
    }

    public function test_migrate_fresh_creates_full_schema(): void
    {
        // RefreshDatabase already runs every migration up before each test,
        // so the full schema being present is the assertion itself.
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('restaurants'));
        $this->assertTrue(Schema::hasTable('outlets'));
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasTable('permissions'));
        $this->assertTrue(Schema::hasTable('user_outlets'));
        $this->assertTrue(Schema::hasTable('units'));
        $this->assertTrue(Schema::hasTable('languages'));
    }

    public function test_outlet_unique_code_per_restaurant(): void
    {
        $r = Restaurant::factory()->create();
        Outlet::factory()->create(['restaurant_id' => $r->id, 'code' => 'T1']);

        $this->expectException(QueryException::class);
        Outlet::factory()->create(['restaurant_id' => $r->id, 'code' => 'T1']);
    }

    public function test_restaurant_has_many_outlets_relation(): void
    {
        $r = Restaurant::factory()->create();
        Outlet::factory()->count(2)->create(['restaurant_id' => $r->id]);

        $this->assertCount(2, $r->fresh()->outlets);
    }

    public function test_user_soft_deletes(): void
    {
        $u = User::factory()->create();
        $u->delete();
        $this->assertSoftDeleted('users', ['id' => $u->id]);
        $this->assertNull(User::find($u->id));
        $this->assertNotNull(User::withTrashed()->find($u->id));
    }

    public function test_user_belongs_to_restaurant(): void
    {
        $r = Restaurant::factory()->create();
        $u = User::factory()->create(['restaurant_id' => $r->id]);

        $this->assertSame($r->id, $u->fresh()->restaurant->id);
    }

    public function test_user_outlets_pivot_assigns(): void
    {
        $r = Restaurant::factory()->create();
        $o = Outlet::factory()->create(['restaurant_id' => $r->id]);
        $u = User::factory()->create(['restaurant_id' => $r->id]);

        $u->outlets()->attach($o->id);

        $this->assertCount(1, $u->fresh()->outlets);
        $this->assertSame($o->id, $u->outlets->first()->id);
    }

    public function test_tax_config_decimal_casts(): void
    {
        $r = Restaurant::factory()->create();
        $t = TaxConfig::create([
            'restaurant_id' => $r->id,
            'name' => 'GST 5',
            'type' => 'intra_state',
            'cgst_rate' => 2.5,
            'sgst_rate' => 2.5,
            'igst_rate' => 0,
            'cess_rate' => 0,
            'is_active' => true,
        ]);

        $this->assertSame('2.50', (string) $t->cgst_rate);
    }
}
