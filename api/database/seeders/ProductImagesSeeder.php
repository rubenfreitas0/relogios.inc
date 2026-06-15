<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductImage;
use App\Models\Product;

class ProductImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            ProductImage::factory()->count(15)->create();
            return;
        }

        foreach ($products as $product) {
            // Create a primary image for each product
            ProductImage::factory()->create([
                'product_id' => $product->id,
                'is_primary' => true,
                'sort_order' => 1,
            ]);

            // Create 1-2 additional gallery images
            ProductImage::factory()->count(rand(1, 2))->create([
                'product_id' => $product->id,
                'is_primary' => false,
                'sort_order' => fn() => fake()->numberBetween(2, 5),
            ]);
        }
    }
}
