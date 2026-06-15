<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class OrderTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    /**
     * Helper: cria a infraestrutura de shipping uma vez e devolve o método.
     * Reutiliza o existente se já houver um.
     */
    private function getOrCreateShipping(): ShippingMethod
    {
        $method = ShippingMethod::first();

        if ($method) {
            return $method;
        }

        $shipping = $this->setupShipping();
        return $shipping['shippingMethod'];
    }

    /**
     * Helper: cria uma Order completa via checkout para um user.
     */
    private function createOrderForUser(array $userAuth): Order
    {
        $shippingMethod = $this->getOrCreateShipping();
        $product = $this->createProduct(['stock' => 50, 'weight' => 0.5]);
        $this->addToCart($userAuth['user'], $product, 1);

        Sanctum::actingAs($userAuth['user']);
        $this->performCheckout([], $shippingMethod->id);

        return Order::latest()->first();
    }

    // LISTAR ENCOMENDAS

    public function test_can_list_own_orders(): void
    {
        $userAuth = $this->createUser();
        $this->createOrderForUser($userAuth);
        $this->createOrderForUser($userAuth);

        Sanctum::actingAs($userAuth['user']);
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_cannot_see_other_users_orders(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser(['email' => 'outro@teste.com']);

        $this->createOrderForUser($userA);
        $this->createOrderForUser($userB);

        // User A só vê as dele
        Sanctum::actingAs($userA['user']);
        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    // DETALHE DE ENCOMENDA

    public function test_can_show_order_detail(): void
    {
        $userAuth = $this->createUser();
        $order = $this->createOrderForUser($userAuth);

        Sanctum::actingAs($userAuth['user']);
        $response = $this->getJson('/api/orders/' . $order->order_number);

        $response->assertStatus(200)
            ->assertJsonPath('data.order_number', $order->order_number)
            ->assertJsonStructure([
                'data' => [
                    'order_number',
                    'status',
                    'payment_status',
                    'customer',
                    'shipping_address',
                    'subtotal',
                    'total',
                    'items',
                ],
            ]);
    }

    public function test_cannot_show_other_users_order(): void
    {
        $userA = $this->createUser();
        $userB = $this->createUser(['email' => 'outro@teste.com']);

        $order = $this->createOrderForUser($userA);

        // User B tenta ver a encomenda de User A
        Sanctum::actingAs($userB['user']);
        $response = $this->getJson('/api/orders/' . $order->order_number);

        $response->assertStatus(404);
    }

    // ACESSO
    public function test_guest_cannot_list_orders(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401);
    }
}
