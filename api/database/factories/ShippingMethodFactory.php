<?php

namespace Database\Factories;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'name' => fake()->randomElement(['Normal', 'Expresso', 'Standard', 'Premium']),
            'carrier' => fake()->randomElement(['CTT', 'DHL', 'DPD', 'FedEx']),
            'price' => fake()->randomFloat(2, 2, 50),
            'min_weight' => 0.000,
            'max_weight' => fake()->randomFloat(3, 5, 100),
            'estimated_days' => fake()->randomElement(['1-2 dias úteis', '3-5 dias úteis', '5-7 dias úteis']),
            'is_active' => true,
        ];
    }
}
