<?php

namespace Database\Factories;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TaxRate>
 */
class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countryCode = fake()->unique()->countryCode();
        return [
            'country_code' => $countryCode,
            'name' => 'IVA ' . $countryCode,
            'rate' => fake()->randomElement([6.00, 13.00, 23.00, 21.00, 19.00]),
            'is_active' => true,
        ];
    }
}
