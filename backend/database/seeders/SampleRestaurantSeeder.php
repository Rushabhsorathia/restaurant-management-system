<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class SampleRestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::firstOrCreate(
            ['email' => 'demo@rms.local'],
            [
                'name' => 'Demo Restaurant',
                'legal_name' => 'Demo Restaurant Pvt Ltd',
                'gstin' => '24ABCDE1234F1Z5',
                'pan' => 'ABCDE1234F',
                'email' => 'demo@rms.local',
                'phone' => '+91-9876543210',
                'address_line1' => '12, MG Road',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380001',
                'country' => 'India',
                'currency_code' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'default_tax_rate' => 5.00,
                'fssai_number' => '12345678901234',
            ],
        );

        Outlet::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'code' => 'DINEIN-01'],
            [
                'name' => 'Main Branch',
                'type' => 'dine_in',
                'phone' => '+91-9876543211',
                'address_line1' => '12, MG Road',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380001',
                'is_central_kitchen' => false,
                'is_active' => true,
            ],
        );

        Outlet::firstOrCreate(
            ['restaurant_id' => $restaurant->id, 'code' => 'CK-01'],
            [
                'name' => 'Cloud Kitchen',
                'type' => 'cloud_kitchen',
                'phone' => '+91-9876543212',
                'address_line1' => '14, Industrial Estate',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380015',
                'is_central_kitchen' => true,
                'is_active' => true,
            ],
        );
    }
}
