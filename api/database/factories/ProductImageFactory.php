<?php

namespace Database\Factories;

use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'url' => 'https://via.placeholder.com/640x480',
            'is_primary' => false,
            'sort_order' => fake()->numberBetween(1, 5),
        ];
    }
}
