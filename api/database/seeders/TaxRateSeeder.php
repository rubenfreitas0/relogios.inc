<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxRateSeeder extends Seeder
{
    /**
     * Seed das taxas de IVA standard por país da UE.
     *
     * Valores atualizados a 2025. Fonte: Comissão Europeia.
     */
    public function run(): void
    {
        $rates = [
            // Zona 1 — Portugal Continental
            ['country_code' => 'PT', 'name' => 'IVA',  'rate' => 23.00],

            // Zona 3 — Espanha
            ['country_code' => 'ES', 'name' => 'IVA',  'rate' => 21.00],

            // Zona 4 — Resto da UE
            ['country_code' => 'DE', 'name' => 'MwSt', 'rate' => 19.00],
            ['country_code' => 'FR', 'name' => 'TVA',  'rate' => 20.00],
            ['country_code' => 'IT', 'name' => 'IVA',  'rate' => 22.00],
            ['country_code' => 'NL', 'name' => 'BTW',  'rate' => 21.00],
            ['country_code' => 'BE', 'name' => 'TVA',  'rate' => 21.00],
            ['country_code' => 'AT', 'name' => 'USt',  'rate' => 20.00],
            ['country_code' => 'IE', 'name' => 'VAT',  'rate' => 23.00],
            ['country_code' => 'LU', 'name' => 'TVA',  'rate' => 17.00],
            ['country_code' => 'FI', 'name' => 'ALV',  'rate' => 25.50],
            ['country_code' => 'GR', 'name' => 'ΦΠΑ',  'rate' => 24.00],
            ['country_code' => 'SK', 'name' => 'DPH',  'rate' => 23.00],
            ['country_code' => 'SI', 'name' => 'DDV',  'rate' => 22.00],
            ['country_code' => 'EE', 'name' => 'KM',   'rate' => 22.00],
            ['country_code' => 'LV', 'name' => 'PVN',  'rate' => 21.00],
            ['country_code' => 'LT', 'name' => 'PVM',  'rate' => 21.00],
            ['country_code' => 'CY', 'name' => 'ΦΠΑ',  'rate' => 19.00],
            ['country_code' => 'MT', 'name' => 'VAT',  'rate' => 18.00],
            ['country_code' => 'HR', 'name' => 'PDV',  'rate' => 25.00],
            ['country_code' => 'BG', 'name' => 'ДДС',  'rate' => 20.00],
            ['country_code' => 'RO', 'name' => 'TVA',  'rate' => 19.00],
            ['country_code' => 'CZ', 'name' => 'DPH',  'rate' => 21.00],
            ['country_code' => 'DK', 'name' => 'MOMS', 'rate' => 25.00],
            ['country_code' => 'SE', 'name' => 'MOMS', 'rate' => 25.00],
            ['country_code' => 'PL', 'name' => 'VAT',  'rate' => 23.00],
            ['country_code' => 'HU', 'name' => 'ÁFA',  'rate' => 27.00],
        ];

        DB::table('tax_rates')->insert(
            array_map(fn($r) => array_merge($r, [
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]), $rates)
        );
    }
}
