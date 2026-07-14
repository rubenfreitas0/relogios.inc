<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class ShippingTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // LISTAR MÉTODOS DE ENVIO

    public function test_can_list_shipping_methods(): void
    {
        $this->setupShipping();

        // Método inativo — não deve aparecer
        ShippingMethod::factory()->create(['is_active' => false]);

        $response = $this->getJson('/api/shipping');

        $response->assertStatus(200);

        $methods = $response->json();
        $this->assertCount(1, $methods);
        $this->assertEquals('CTT Normal', $methods[0]['name']);
    }

    // CALCULAR PORTES

    public function test_can_calculate_shipping(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $this->setupShipping();
        $product = $this->createProduct(['price' => 100.00, 'weight' => 0.5]);
        $this->addToCart($user, $product, 2);

        $response = $this->getJson('/api/shipping/calculate?country=PT&postal_code=1000-001', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'subtotal',
                'total_weight',
                'shipping_methods',
            ])
            ->assertJsonPath('subtotal', 200)
            ->assertJsonPath('total_weight', 1);
    }

    public function test_calculate_with_empty_cart(): void
    {
        ['headers' => $headers] = $this->createUser();
        $this->setupShipping();

        $response = $this->getJson('/api/shipping/calculate?country=PT', $headers);

        $response->assertStatus(422);
    }

    public function test_islands_zone_resolution(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['price' => 100.00, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        // Configurar zona Continental
        $this->setupShipping();

        // Configurar zona Ilhas
        $islandsZone = ShippingZone::factory()->create([
            'name'      => 'Ilhas (Açores e Madeira)',
            'is_active' => true,
        ]);

        $islandsMethod = ShippingMethod::factory()->create([
            'shipping_zone_id' => $islandsZone->id,
            'name'             => 'CTT Ilhas',
            'carrier'          => 'CTT',
            'price'            => 8.00,
            'min_weight'       => 0.000,
            'max_weight'       => 30.000,
            'is_active'        => true,
        ]);


        // Código postal 9000-000 → Ilhas
        $response = $this->getJson('/api/shipping/calculate?country=PT&postal_code=9000-000', $headers);

        $response->assertStatus(200);

        $methodNames = collect($response->json('shipping_methods'))->pluck('name')->all();
        $this->assertContains('CTT Ilhas', $methodNames);
    }

    public function test_continental_zone_resolution(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['price' => 100.00, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        $this->setupShipping();

        // Código postal 1000-001 → Continental
        $response = $this->getJson('/api/shipping/calculate?country=PT&postal_code=1000-001', $headers);

        $response->assertStatus(200);

        $methodNames = collect($response->json('shipping_methods'))->pluck('name')->all();
        $this->assertContains('CTT Normal', $methodNames);
    }
}
