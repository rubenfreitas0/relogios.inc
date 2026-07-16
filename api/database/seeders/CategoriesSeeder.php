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
            // Tipo de relógio
            ['name' => 'Clássicos', 'slug' => 'classicos', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Desporto', 'slug' => 'desporto', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Casual', 'slug' => 'casual', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Mergulho', 'slug' => 'mergulho', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Aviador', 'slug' => 'aviador', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Cronógrafos', 'slug' => 'cronografos', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Militar', 'slug' => 'militar', 'group' => 'tipo', 'is_active' => true],
            ['name' => 'Automáticos', 'slug' => 'automaticos', 'group' => 'tipo', 'is_active' => true],

            // Mecanismo
            ['name' => 'Analógico', 'slug' => 'analogico', 'group' => 'mecanismo', 'is_active' => true],
            ['name' => 'Digital', 'slug' => 'digital', 'group' => 'mecanismo', 'is_active' => true],
            ['name' => 'Analógico-Digital', 'slug' => 'analogico-digital', 'group' => 'mecanismo', 'is_active' => true],
            ['name' => 'Smartwatch', 'slug' => 'smartwatch', 'group' => 'mecanismo', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
