<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    /**
     * Seed das zonas de envio e respectivos países.
     *
     * Zona 1: Portugal Continental (PT)
     * Zona 2: Ilhas — Açores e Madeira (distinção por código postal — fase 2)
     * Zona 3: Espanha (ES)
     * Zona 4: Resto da UE
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
        // Nota: Ainda sem country_code associado.
        // A distinção Continente vs Ilhas será feita por código postal (9xxx) numa fase 2.
        // Por agora, PT é tratado como Continental.
        ShippingZone::create([
            'name'      => 'Ilhas (Açores e Madeira)',
            'is_active' => true,
        ]);

        // ── Zona 3: Espanha ──
        $espanha = ShippingZone::create([
            'name'      => 'Espanha',
            'is_active' => true,
        ]);

        ShippingZoneCountry::create([
            'shipping_zone_id' => $espanha->id,
            'country_code'     => 'ES',
        ]);

        // ── Zona 4: Resto da UE ──
        $restoUe = ShippingZone::create([
            'name'      => 'Resto da UE',
            'is_active' => true,
        ]);

        $euCountries = [
            'DE', // Alemanha
            'FR', // França
            'IT', // Itália
            'NL', // Países Baixos
            'BE', // Bélgica
            'AT', // Áustria
            'IE', // Irlanda
            'LU', // Luxemburgo
            'FI', // Finlândia
            'GR', // Grécia
            'SK', // Eslováquia
            'SI', // Eslovénia
            'EE', // Estónia
            'LV', // Letónia
            'LT', // Lituânia
            'CY', // Chipre
            'MT', // Malta
            'HR', // Croácia
            'BG', // Bulgária
            'RO', // Roménia
            'CZ', // República Checa
            'DK', // Dinamarca
            'SE', // Suécia
            'PL', // Polónia
            'HU', // Hungria
        ];

        $zoneCountries = array_map(fn(string $code) => [
            'shipping_zone_id' => $restoUe->id,
            'country_code'     => $code,
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $euCountries);

        ShippingZoneCountry::insert($zoneCountries);
    }
}
