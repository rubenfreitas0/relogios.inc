<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test that all factories are well-defined and can create records.
     */
    public function test_all_factories_can_create_models(): void
    {
        $this->assertNotNull(\App\Models\User::factory()->create());
        $this->assertNotNull(\App\Models\Brand::factory()->create());
        $this->assertNotNull(\App\Models\Category::factory()->create());
        $this->assertNotNull(\App\Models\Product::factory()->create());
        $this->assertNotNull(\App\Models\ProductImage::factory()->create());
        $this->assertNotNull(\App\Models\Address::factory()->create());
        $this->assertNotNull(\App\Models\CartItem::factory()->create());
        $this->assertNotNull(\App\Models\Order::factory()->create());
        $this->assertNotNull(\App\Models\OrderItem::factory()->create());
        $this->assertNotNull(\App\Models\Payment::factory()->create());
        $this->assertNotNull(\App\Models\ShippingZone::factory()->create());
        $this->assertNotNull(\App\Models\ShippingZoneCountry::factory()->create());
        $this->assertNotNull(\App\Models\ShippingMethod::factory()->create());
        $this->assertNotNull(\App\Models\TaxRate::factory()->create());
        $this->assertNotNull(\App\Models\Ticket::factory()->create());
    }
}
