<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'legal_name' => $name.' '.Str::random(3).' Pvt Ltd',
            'gstin' => Str::upper(Str::random(15)),
            'pan' => Str::upper(Str::random(10)),
            'email' => fake()->unique()->companyEmail(),
            'phone' => '+91'.fake()->numerify('##########'),
            'address_line1' => fake()->streetAddress(),
            'city' => fake()->randomElement(['Ahmedabad', 'Mumbai', 'Bengaluru', 'Pune']),
            'state' => fake()->randomElement(['Gujarat', 'Maharashtra', 'Karnataka']),
            'pincode' => fake()->numerify('######'),
            'country' => 'India',
            'currency_code' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'default_tax_rate' => 5.00,
            'fssai_number' => fake()->numerify('##############'),
        ];
    }
}
