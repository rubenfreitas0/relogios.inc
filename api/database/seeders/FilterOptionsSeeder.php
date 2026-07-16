<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FilterOption;

/**
 * Semeia as opções de filtro das páginas de categoria (homens/mulheres/unisexo).
 * Estas opções são fixas: o admin pode editá-las, mas não criar nem eliminar.
 */
class FilterOptionsSeeder extends Seeder
{
    public function run(): void
    {
        FilterOption::query()->delete();

        $data = [
            'homens' => [
                'marca' => ['Rolex', 'Patek Philippe', 'Audemars Piguet', 'Omega', 'Cartier', 'Seiko', 'Casio'],
                'tipo' => [
                    ['Clássico', 'classicos'],
                    ['Desportivo', 'desporto'],
                    ['Casual', 'casual'],
                    ['Mergulho', 'mergulho'],
                    ['Aviador', 'aviador'],
                    ['Cronógrafo', 'cronografos'],
                    ['Militar', 'militar'],
                ],
                'mecanismo' => [
                    ['Analógico', 'analogico'],
                    ['Digital', 'digital'],
                    ['Analógico-Digital', 'analogico-digital'],
                    ['Smartwatch', 'smartwatch'],
                ],
                'preco' => [
                    ['Até €100', 0, 100],
                    ['€100 – €250', 100, 250],
                    ['€250 – €500', 250, 500],
                    ['Acima de €500', 500, 999999],
                ],
                'cor' => [
                    ['Preto', '#1a1a1a'],
                    ['Prata', '#c0c0c0'],
                    ['Dourado', '#c8a44a'],
                    ['Azul', '#1e3a5f'],
                    ['Verde', '#2d5a3d'],
                    ['Branco', '#f0f0f0'],
                ],
            ],
            'mulheres' => [
                'marca' => ['Cartier', 'Rolex', 'Omega', 'Chopard', 'Bulgari', 'Longines', 'Seiko'],
                'tipo' => [
                    ['Clássico', 'classicos'],
                    ['Elegante', 'classicos'],
                    ['Casual', 'casual'],
                    ['Desportivo', 'desporto'],
                    ['Minimalista', 'classicos'],
                    ['Cronógrafo', 'cronografos'],
                ],
                'mecanismo' => [
                    ['Analógico', 'analogico'],
                    ['Digital', 'digital'],
                    ['Smartwatch', 'smartwatch'],
                ],
                'preco' => [
                    ['Até €80', 0, 80],
                    ['€80 – €200', 80, 200],
                    ['€200 – €450', 200, 450],
                    ['Acima de €450', 450, 999999],
                ],
                'cor' => [
                    ['Dourado', '#c8a44a'],
                    ['Rosa Gold', '#b76e79'],
                    ['Prata', '#c0c0c0'],
                    ['Branco', '#f0f0f0'],
                    ['Preto', '#1a1a1a'],
                    ['Rose', '#e8a0a0'],
                ],
            ],
            'unisexo' => [
                'marca' => ['Rolex', 'Omega', 'Cartier', 'TAG Heuer', 'Tudor', 'Seiko', 'Casio'],
                'tipo' => [
                    ['Casual', 'casual'],
                    ['Desportivo', 'desporto'],
                    ['Smartwatch', 'smartwatch'],
                    ['Minimalista', 'classicos'],
                    ['Vintage', 'classicos'],
                    ['Outdoor', 'desporto'],
                ],
                'mecanismo' => [
                    ['Analógico', 'analogico'],
                    ['Digital', 'digital'],
                    ['Smartwatch', 'smartwatch'],
                    ['Híbrido', 'automaticos'],
                ],
                'preco' => [
                    ['Até €80', 0, 80],
                    ['€80 – €200', 80, 200],
                    ['€200 – €500', 200, 500],
                    ['Acima de €500', 500, 999999],
                ],
                'cor' => [
                    ['Preto', '#1a1a1a'],
                    ['Branco', '#f0f0f0'],
                    ['Prata', '#c0c0c0'],
                    ['Laranja', '#d4621a'],
                    ['Verde', '#2d5a3d'],
                    ['Azul', '#1e3a5f'],
                ],
            ],
        ];

        foreach ($data as $gender => $groups) {
            // Marcas: value = slug gerado a partir do nome
            foreach ($groups['marca'] as $i => $name) {
                FilterOption::create([
                    'gender'     => $gender,
                    'group'      => 'marca',
                    'label'      => $name,
                    'value'      => str_replace(' ', '-', strtolower($name)),
                    'sort_order' => $i,
                ]);
            }

            foreach (['tipo', 'mecanismo'] as $group) {
                foreach ($groups[$group] as $i => [$label, $slug]) {
                    FilterOption::create([
                        'gender'     => $gender,
                        'group'      => $group,
                        'label'      => $label,
                        'value'      => $slug,
                        'sort_order' => $i,
                    ]);
                }
            }

            foreach ($groups['preco'] as $i => [$label, $min, $max]) {
                FilterOption::create([
                    'gender'     => $gender,
                    'group'      => 'preco',
                    'label'      => $label,
                    'meta'       => ['min' => $min, 'max' => $max],
                    'sort_order' => $i,
                ]);
            }

            foreach ($groups['cor'] as $i => [$name, $hex]) {
                FilterOption::create([
                    'gender'     => $gender,
                    'group'      => 'cor',
                    'label'      => $name,
                    'value'      => $hex,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
