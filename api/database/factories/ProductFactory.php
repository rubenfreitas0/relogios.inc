<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Brand;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'gender' => fake()->randomElement(['masculino', 'feminino', 'unisexo']),
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'features' => fake()->paragraphs(2, true),
            'in_the_box' => [
                'Relógio ' . fake()->word(),
                'Caixa de apresentação premium',
                'Manual de instruções',
                'Certificado de garantia'
            ],
            'price' => fake()->numberBetween(100, 10000),
            'stock' => fake()->numberBetween(1, 100),
            'weight' => fake()->randomFloat(3, 0.1, 5),
            'is_active' => true,
            'is_featured' => false,
        ];
    }

    /**
     * Associa categorias ao produto (pivot category_product) após a criação.
     *
     * @param array<int> $categoryIds
     */
    public function withCategories(array $categoryIds): static
    {
        return $this->afterCreating(function (Product $product) use ($categoryIds) {
            $product->categories()->sync($categoryIds);
        });
    }
}
