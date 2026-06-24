<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Outlet;
use App\Models\Restaurant;
use App\Models\TaxConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_seed_populates_expected_records(): void
    {
        Artisan::call('db:seed');

        $this->assertSame(7, Role::count());
        $this->assertGreaterThan(50, Permission::count());
        $this->assertSame(1, Restaurant::count());
        $this->assertSame(2, Outlet::count());
        $this->assertSame(1, User::where('email', 'admin@rms.local')->count());
        $this->assertSame(7, \DB::table('units')->count());
        $this->assertSame(3, \DB::table('languages')->count());
        $this->assertSame(5, TaxConfig::count());

        $admin = User::where('email', 'admin@rms.local')->firstOrFail();
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole('hq_admin'));
        $this->assertTrue($admin->can('menu.create'));
    }

    public function test_seeders_are_idempotent(): void
    {
        Artisan::call('db:seed');
        $counts = [
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'restaurants' => Restaurant::count(),
            'outlets' => Outlet::count(),
            'users' => User::count(),
            'units' => \DB::table('units')->count(),
            'languages' => \DB::table('languages')->count(),
        ];

        Artisan::call('db:seed');

        $this->assertSame($counts['roles'], Role::count());
        $this->assertSame($counts['permissions'], Permission::count());
        $this->assertSame($counts['restaurants'], Restaurant::count());
        $this->assertSame($counts['outlets'], Outlet::count());
        $this->assertSame($counts['users'], User::count());
        $this->assertSame($counts['units'], \DB::table('units')->count());
        $this->assertSame($counts['languages'], \DB::table('languages')->count());
    }
}
