<?php

namespace App\Services;

use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;

/**
 * Resolve a zona de envio com base no país e código postal.
 * Para PT, distingue Continente vs Ilhas (Açores/Madeira) pelo prefixo do código postal (9xxx).
 *
 * Centraliza a lógica que antes estava duplicada no OrderController e no ShippingController.
 */
class ShippingZoneResolver
{
    /**
     * Devolve o ID da zona de envio correspondente ao país e código postal.
     */
    public function resolve(string $countryCode, ?string $postalCode): ?int
    {
        $countryCode = strtoupper($countryCode);

        // Distinção PT: Ilhas (código postal começa com 9) vs Continental
        if ($countryCode === 'PT' && $postalCode && str_starts_with(trim($postalCode), '9')) {
            $islandsZone = ShippingZone::where('name', 'Ilhas (Açores e Madeira)')
                ->where('is_active', true)
                ->first();

            if ($islandsZone) {
                return $islandsZone->id;
            }
        }

        $zoneCountry = ShippingZoneCountry::where('country_code', $countryCode)->first();

        return $zoneCountry?->shipping_zone_id;
    }
}
