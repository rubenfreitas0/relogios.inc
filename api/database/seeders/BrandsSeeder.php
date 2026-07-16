<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Brand;



class BrandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            ['name' => 'Rolex', 'slug' => 'rolex', 'is_active' => true],
            ['name' => 'Patek Philippe', 'slug' => 'patek-philippe', 'is_active' => true],
            ['name' => 'Audemars Piguet', 'slug' => 'audemars-piguet', 'is_active' => true],
            ['name' => 'Omega', 'slug' => 'omega', 'is_active' => true],
            ['name' => 'Cartier', 'slug' => 'cartier', 'is_active' => true],
            ['name' => 'Seiko', 'slug' => 'seiko', 'is_active' => true],
            ['name' => 'Casio', 'slug' => 'casio', 'is_active' => true],
            ['name' => 'Chopard', 'slug' => 'chopard', 'is_active' => true],
            ['name' => 'Bulgari', 'slug' => 'bulgari', 'is_active' => true],
            ['name' => 'Longines', 'slug' => 'longines', 'is_active' => true],
            ['name' => 'Tag Heuer', 'slug' => 'tag-heuer', 'is_active' => true],
            ['name' => 'Tudor', 'slug' => 'tudor', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }
    }
}
