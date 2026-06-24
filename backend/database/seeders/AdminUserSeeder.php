<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['email' => 'demo@rms.local'],
            ['name' => 'Demo Restaurant', 'legal_name' => 'Demo Restaurant Pvt Ltd']
        );

        User::updateOrCreate(
            ['email' => 'admin@rms.local'],
            [
                'restaurant_id' => $restaurant->id,
                'name' => 'Default Admin',
                'phone' => '+91-9000000000',
                'password' => Hash::make('password'),
                'is_active' => true,
                'must_change_password' => true,
            ],
        );
    }
}
