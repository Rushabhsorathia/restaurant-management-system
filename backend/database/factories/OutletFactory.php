<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'name' => fake()->randomElement(['Main Branch', 'Cloud Kitchen', 'Express', 'Downtown']),
            'code' => strtoupper(fake()->unique()->bothify('??##')),
            'type' => fake()->randomElement(['dine_in', 'qsr', 'cloud_kitchen', 'food_court']),
            'phone' => '+91'.fake()->numerify('##########'),
            'address_line1' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'pincode' => fake()->numerify('######'),
            'is_central_kitchen' => false,
            'is_active' => true,
        ];
    }
}
