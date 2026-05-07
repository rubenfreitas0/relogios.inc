<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;



class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Automáticos', 'slug' => 'automaticos', 'is_active' => true],
            ['name' => 'Cronógrafos', 'slug' => 'cronografos', 'is_active' => true],
            ['name' => 'Mergulho', 'slug' => 'mergulho', 'is_active' => true],
            ['name' => 'Desporto', 'slug' => 'desporto', 'is_active' => true],
            ['name' => 'Clássicos', 'slug' => 'classicos', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
