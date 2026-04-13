<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;


/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => fake()->unique()->numerify('ORD-#########'),
            'status' => 'pending',
            'payment_status' => 'pending',
            'shipping_firstname' => fake()->firstName(),
            'shipping_lastname' => fake()->lastName(),
            'shipping_phone' => fake()->phoneNumber(),
            'shipping_address_line1' => fake()->streetAddress(),
            'shipping_address_line2' => null,
            'shipping_city' => fake()->city(),
            'shipping_postal_code' => fake()->postcode(),
            'shipping_country' => fake()->countryCode(),
            'nif' => fake()->numerify('#########'),
            'subtotal' => fake()->numberBetween(100, 10000),
            'shipping_cost' => fake()->numberBetween(10, 100),
            'total' => fake()->numberBetween(100, 10000),
            'tracking_number' => null,
            'paid_at' => null,
        ];
    }
}
