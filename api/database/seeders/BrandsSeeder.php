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
            ['name' => 'Rolex', 'slug' => 'rolex', 'logo' => 'brands/rolex.png', 'is_active' => true],
            ['name' => 'Casio', 'slug' => 'casio', 'logo' => 'brands/casio.png', 'is_active' => true],
            ['name' => 'Seiko', 'slug' => 'seiko', 'logo' => 'brands/seiko.png', 'is_active' => true],
            ['name' => 'Omega', 'slug' => 'omega', 'logo' => 'brands/omega.png', 'is_active' => true],
            ['name' => 'Tag Heuer', 'slug' => 'tag-heuer', 'logo' => 'brands/tag-heuer.png', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
