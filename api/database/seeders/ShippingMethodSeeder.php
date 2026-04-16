<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            [
                'name'           => 'CTT Normal',
                'carrier'        => 'CTT',
                'price'          => 3.50,
                'min_weight'     => 0.000,
                'max_weight'     => 0.500,
                'estimated_days' => '3-5 dias úteis',
                'is_active'      => true,
            ],
            [
                'name'           => 'CTT Normal',
                'carrier'        => 'CTT',
                'price'          => 4.50,
                'min_weight'     => 0.501,
                'max_weight'     => 1.000,
                'estimated_days' => '3-5 dias úteis',
                'is_active'      => true,
            ],
            [
                'name'           => 'CTT Normal',
                'carrier'        => 'CTT',
                'price'          => 6.00,
                'min_weight'     => 1.001,
                'max_weight'     => 2.000,
                'estimated_days' => '3-5 dias úteis',
                'is_active'      => true,
            ],
            [
                'name'           => 'DPD Classic',
                'carrier'        => 'DPD',
                'price'          => 8.00,
                'min_weight'     => 2.001,
                'max_weight'     => 5.000,
                'estimated_days' => '2-3 dias úteis',
                'is_active'      => true,
            ],
            [
                'name'           => 'DPD Classic',
                'carrier'        => 'DPD',
                'price'          => 12.00,
                'min_weight'     => 5.001,
                'max_weight'     => 10.000,
                'estimated_days' => '2-3 dias úteis',
                'is_active'      => true,
            ],
            [
                'name'           => 'DHL Expresso',
                'carrier'        => 'DHL',
                'price'          => 20.00,
                'min_weight'     => 0.000,
                'max_weight'     => 99.999,
                'estimated_days' => '1-2 dias úteis',
                'is_active'      => true,
            ],
        ];

        DB::table('shipping_methods')->insert(
            array_map(fn($m) => array_merge($m, [
                'created_at' => now(),
                'updated_at' => now(),
            ]), $methods)
        );
    }
}
