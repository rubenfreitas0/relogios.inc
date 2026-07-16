<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    /**
     * Zonas de envio (simplificadas):
     *
     * Zona 1: Portugal Continental (PT)
     * Zona 2: Ilhas — Açores e Madeira (distinção por código postal — fase 2)
     * Zona 3: Europa — todos os restantes países europeus, tratados como uma região
     */
    public function run(): void
    {
        // ── Zona 1: Portugal Continental ──
        $ptContinental = ShippingZone::create([
            'name'      => 'Portugal Continental',
            'is_active' => true,
        ]);

        ShippingZoneCountry::create([
            'shipping_zone_id' => $ptContinental->id,
            'country_code'     => 'PT',
        ]);

        // ── Zona 2: Ilhas (Açores + Madeira) ──
        // Sem country_code: a distinção Continente vs Ilhas é feita por código
        // postal (9xxx) numa fase 2. Por agora PT resolve para Continental.
        ShippingZone::create([
            'name'      => 'Ilhas (Açores e Madeira)',
            'is_active' => true,
        ]);

        // ── Zona 3: Europa (região única) ──
        $europa = ShippingZone::create([
            'name'      => 'Europa',
            'is_active' => true,
        ]);

        $europaCountries = [
            // UE
            'ES', 'DE', 'FR', 'IT', 'NL', 'BE', 'AT', 'IE', 'LU', 'FI', 'GR',
            'SK', 'SI', 'EE', 'LV', 'LT', 'CY', 'MT', 'HR', 'BG', 'RO', 'CZ',
            'DK', 'SE', 'PL', 'HU',
            // Europa fora da UE
            'GB', 'CH', 'NO', 'IS', 'LI', 'AD', 'MC', 'SM',
        ];

        ShippingZoneCountry::insert(array_map(fn(string $code) => [
            'shipping_zone_id' => $europa->id,
            'country_code'     => $code,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $europaCountries));
    }
}
