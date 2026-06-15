<?php

namespace Database\Factories;

use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingZoneCountry>
 */
class ShippingZoneCountryFactory extends Factory
{
    protected $model = ShippingZoneCountry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'country_code' => fake()->unique()->countryCode(),
        ];
    }
}
