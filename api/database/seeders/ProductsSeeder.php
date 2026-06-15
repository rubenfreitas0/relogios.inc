<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Brand;
use App\Models\Category;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = Brand::all();
        $categories = Category::all();

        if ($brands->isEmpty() || $categories->isEmpty()) {
            Product::factory()->count(10)->create();
            return;
        }

        Product::factory()->count(10)->create([
            'brand_id' => fn() => $brands->random()->id,
            'category_id' => fn() => $categories->random()->id,
        ]);
    }
}
