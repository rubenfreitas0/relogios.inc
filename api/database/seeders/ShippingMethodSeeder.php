<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Métodos de envio por zona e faixa de peso.
     * Zonas: Portugal Continental, Ilhas (Açores e Madeira), Europa.
     *
     * Depende de ShippingZoneSeeder ter sido executado primeiro.
     */
    public function run(): void
    {
        $ptContinental = ShippingZone::where('name', 'Portugal Continental')->first();
        $ilhas         = ShippingZone::where('name', 'Ilhas (Açores e Madeira)')->first();
        $europa        = ShippingZone::where('name', 'Europa')->first();

        $methods = [
            // ── Portugal Continental — CTT (pesos pequenos) ──
            [
                'shipping_zone_id' => $ptContinental?->id,
                'name'             => 'CTT Normal',
                'carrier'          => 'CTT',
                'price'            => 3.50,
                'min_weight'       => 0.000,
                'max_weight'       => 0.500,
                'estimated_days'   => '3-5 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ptContinental?->id,
                'name'             => 'CTT Normal',
                'carrier'          => 'CTT',
                'price'            => 4.50,
                'min_weight'       => 0.501,
                'max_weight'       => 1.000,
                'estimated_days'   => '3-5 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ptContinental?->id,
                'name'             => 'CTT Normal',
                'carrier'          => 'CTT',
                'price'            => 6.00,
                'min_weight'       => 1.001,
                'max_weight'       => 2.000,
                'estimated_days'   => '3-5 dias úteis',
                'is_active'        => true,
            ],
            // ── Portugal Continental — DPD (pesos maiores) ──
            [
                'shipping_zone_id' => $ptContinental?->id,
                'name'             => 'DPD Classic',
                'carrier'          => 'DPD',
                'price'            => 8.00,
                'min_weight'       => 2.001,
                'max_weight'       => 5.000,
                'estimated_days'   => '2-3 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ptContinental?->id,
                'name'             => 'DPD Classic',
                'carrier'          => 'DPD',
                'price'            => 12.00,
                'min_weight'       => 5.001,
                'max_weight'       => 10.000,
                'estimated_days'   => '2-3 dias úteis',
                'is_active'        => true,
            ],

            // ── Ilhas (Açores e Madeira) — CTT ──
            [
                'shipping_zone_id' => $ilhas?->id,
                'name'             => 'CTT Ilhas',
                'carrier'          => 'CTT',
                'price'            => 6.00,
                'min_weight'       => 0.000,
                'max_weight'       => 0.500,
                'estimated_days'   => '5-8 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ilhas?->id,
                'name'             => 'CTT Ilhas',
                'carrier'          => 'CTT',
                'price'            => 8.50,
                'min_weight'       => 0.501,
                'max_weight'       => 1.000,
                'estimated_days'   => '5-8 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ilhas?->id,
                'name'             => 'CTT Ilhas',
                'carrier'          => 'CTT',
                'price'            => 12.00,
                'min_weight'       => 1.001,
                'max_weight'       => 2.000,
                'estimated_days'   => '5-8 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ilhas?->id,
                'name'             => 'CTT Ilhas Volumoso',
                'carrier'          => 'CTT',
                'price'            => 18.00,
                'min_weight'       => 2.001,
                'max_weight'       => 5.000,
                'estimated_days'   => '5-8 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $ilhas?->id,
                'name'             => 'CTT Ilhas Volumoso',
                'carrier'          => 'CTT',
                'price'            => 26.00,
                'min_weight'       => 5.001,
                'max_weight'       => 10.000,
                'estimated_days'   => '5-8 dias úteis',
                'is_active'        => true,
            ],

            // ── Europa — DHL Standard ──
            [
                'shipping_zone_id' => $europa?->id,
                'name'             => 'DHL Standard',
                'carrier'          => 'DHL',
                'price'            => 12.00,
                'min_weight'       => 0.000,
                'max_weight'       => 0.500,
                'estimated_days'   => '5-7 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $europa?->id,
                'name'             => 'DHL Standard',
                'carrier'          => 'DHL',
                'price'            => 15.00,
                'min_weight'       => 0.501,
                'max_weight'       => 1.000,
                'estimated_days'   => '5-7 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $europa?->id,
                'name'             => 'DHL Standard',
                'carrier'          => 'DHL',
                'price'            => 18.00,
                'min_weight'       => 1.001,
                'max_weight'       => 2.000,
                'estimated_days'   => '5-7 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $europa?->id,
                'name'             => 'DHL Standard',
                'carrier'          => 'DHL',
                'price'            => 25.00,
                'min_weight'       => 2.001,
                'max_weight'       => 5.000,
                'estimated_days'   => '5-7 dias úteis',
                'is_active'        => true,
            ],
            [
                'shipping_zone_id' => $europa?->id,
                'name'             => 'DHL Standard',
                'carrier'          => 'DHL',
                'price'            => 35.00,
                'min_weight'       => 5.001,
                'max_weight'       => 10.000,
                'estimated_days'   => '5-7 dias úteis',
                'is_active'        => true,
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
