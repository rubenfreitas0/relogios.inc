<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class CheckoutTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // CHECKOUT - happy path
    public function test_can_checkout_with_manual_address(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['price' => 100.00, 'stock' => 5, 'weight' => 0.5]);
        $this->addToCart($user, $product, 2);

        $response = $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['order_number', 'status', 'total'],
            ]);

        // Verificar que Order, OrderItems e Payment foram criados
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_can_checkout_with_saved_address(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['price' => 100.00, 'stock' => 5, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        $address = Address::factory()->create([
            'user_id'       => $user->id,
            'firstname'     => 'Maria',
            'lastname'      => 'Santos',
            'address_line1' => 'Avenida da Liberdade 100',
            'city'          => 'Lisboa',
            'postal_code'   => '1250-096',
            'country'       => 'PT',
        ]);

        $response = $this->postJson('/api/orders', [
            'shipping_method_id' => $shipping['shippingMethod']->id,
            'payment_method'     => 'multibanco',
            'address_id'         => $address->id,
        ], $headers);

        $response->assertStatus(201);

        // Verificar que os dados da morada guardada foram usados
        $order = Order::first();
        $this->assertEquals('Maria', $order->shipping_firstname);
        $this->assertEquals('Avenida da Liberdade 100', $order->shipping_address_line1);
    }

    // CHECKOUT - payment data
    public function test_checkout_with_multibanco_creates_payment_data(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 5, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        $this->performCheckout($headers, $shipping['shippingMethod']->id, [
            'payment_method' => 'multibanco',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment->payment_data);
        $this->assertArrayHasKey('entity', $payment->payment_data);
        $this->assertArrayHasKey('reference', $payment->payment_data);
        $this->assertArrayHasKey('expires_at', $payment->payment_data);
    }

    public function test_checkout_with_mbway_creates_payment_data(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 5, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        $this->performCheckout($headers, $shipping['shippingMethod']->id, [
            'payment_method' => 'mbway',
            'payment_phone'  => '912345678',
        ]);

        $payment = Payment::first();
        $this->assertNotNull($payment->payment_data);
        $this->assertArrayHasKey('phone', $payment->payment_data);
        $this->assertEquals('912345678', $payment->payment_data['phone']);
    }

    // CHECKOUT - side effects
    public function test_checkout_decrements_stock(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['price' => 50.00, 'stock' => 10, 'weight' => 0.3]);
        $this->addToCart($user, $product, 3);

        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        // Stock original: 10, comprou: 3, restante: 7
        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_checkout_clears_cart(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 2);

        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $this->assertEquals(0, $user->cartItems()->count());
    }

    // CHECKOUT - validations
    public function test_checkout_fails_with_empty_cart(): void
    {
        ['headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();

        $response = $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $response->assertStatus(422);
    }

    public function test_checkout_fails_with_insufficient_stock(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 2, 'weight' => 0.5]);
        $this->addToCart($user, $product, 5); // Pede 5, só há 2

        $response = $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $response->assertStatus(422);
    }

    public function test_checkout_fails_with_inactive_product(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        // Desativar o produto DEPOIS de o adicionar ao carrinho
        $product->update(['is_active' => false]);

        $response = $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $response->assertStatus(422);
    }

    public function test_checkout_fails_with_inactive_shipping_method(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        // Desativar o método de envio
        $shipping['shippingMethod']->update(['is_active' => false]);

        $response = $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $response->assertStatus(422);
    }

    public function test_checkout_fails_with_wrong_zone(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        // Criar zona para Espanha
        $zoneES = ShippingZone::factory()->create(['name' => 'Espanha', 'is_active' => true]);
        ShippingZoneCountry::factory()->create([
            'shipping_zone_id' => $zoneES->id,
            'country_code'     => 'ES',
        ]);
        $methodES = ShippingMethod::factory()->create([
            'shipping_zone_id' => $zoneES->id,
            'name'             => 'Correos',
            'is_active'        => true,
            'min_weight'       => 0,
            'max_weight'       => 30,
        ]);

        // Tentar usar método de Espanha com morada PT
        $response = $this->performCheckout($headers, $methodES->id, [
            'country' => 'PT',
        ]);

        $response->assertStatus(422);
    }

    public function test_checkout_fails_with_excess_weight(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping([
            'min_weight' => 0,
            'max_weight' => 1.000, // Máximo 1kg
        ]);
        // Produto de 5kg
        $product = $this->createProduct(['stock' => 10, 'weight' => 5.000]);
        $this->addToCart($user, $product, 1);

        $response = $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $response->assertStatus(422);
    }

    // CHECKOUT - totals
    public function test_checkout_calculates_correct_totals(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping(); // shipping = 5.00, IVA = 23%
        $product = $this->createProduct(['price' => 100.00, 'stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 2);

        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $order = Order::first();

        // subtotal = 2 x 100 = 200.00
        $this->assertEquals('200.00', $order->subtotal);
        // shipping = 5.00
        $this->assertEquals('5.00', $order->shipping_cost);
        // tax = 200 * 0.23 = 46.00
        $this->assertEquals('46.00', $order->tax_amount);
        // total = 200 + 5 + 46 = 251.00
        $this->assertEquals('251.00', $order->total);
    }

    public function test_checkout_creates_order_items_snapshot(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['name' => 'Rolex Submariner', 'price' => 150.00, 'stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 2);

        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $order = Order::first();
        $orderItem = $order->orderItems->first();

        $this->assertEquals('Rolex Submariner', $orderItem->product_name);
        $this->assertEquals('150.00', $orderItem->unit_price);
        $this->assertEquals('300.00', $orderItem->item_total);
        $this->assertEquals(2, $orderItem->quantity);
    }

    public function test_order_number_is_unique(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();

        // Primeiro checkout
        $product1 = $this->createProduct(['price' => 50.00, 'stock' => 10, 'weight' => 0.3]);
        $this->addToCart($user, $product1, 1);
        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        // Segundo checkout
        $product2 = $this->createProduct(['name' => 'Produto B', 'price' => 80.00, 'stock' => 10, 'weight' => 0.4]);
        $this->addToCart($user, $product2, 1);
        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $orders = Order::all();
        $this->assertCount(2, $orders);
        $this->assertNotEquals($orders[0]->order_number, $orders[1]->order_number);
    }

    public function test_checkout_saves_notes(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['price' => 100.00, 'stock' => 5, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        $response = $this->postJson('/api/orders', [
            'shipping_method_id' => $shipping['shippingMethod']->id,
            'payment_method'     => 'credit_card',
            'firstname'          => 'Ruben',
            'lastname'           => 'Freitas',
            'address_line1'      => 'Rua das Flores 12',
            'city'               => 'Porto',
            'postal_code'        => '4000-000',
            'country'            => 'PT',
            'notes'              => 'Deixar na caixa do correio se não estiver ninguém.',
        ], $headers);

        $response->assertStatus(201);
        $order = Order::latest()->first();
        $this->assertEquals('Deixar na caixa do correio se não estiver ninguém.', $order->notes);
    }
}
