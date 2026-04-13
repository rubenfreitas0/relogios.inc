<?php

namespace Database\Factories;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Order;
use App\Models\Product;


/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'product_image' => fake()->imageUrl(640, 480, 'watches'),
            'unit_price' => fake()->numberBetween(100, 10000),
            'quantity' => fake()->numberBetween(1, 10),
            'item_total' => fake()->numberBetween(100, 10000),
        ];
    }
}
