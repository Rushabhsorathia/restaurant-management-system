<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            PermissionsSeeder::class,
            SampleRestaurantSeeder::class,
            TaxConfigSeeder::class,
            AdminUserSeeder::class,
            UnitsSeeder::class,
            LanguagesSeeder::class,
        ]);

        $admin = User::where('email', 'admin@rms.local')->first();
        if ($admin && ! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
            $admin->assignRole('hq_admin');
        }
    }
}
