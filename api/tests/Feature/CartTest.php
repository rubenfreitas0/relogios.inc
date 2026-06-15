<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class CartTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // LISTAR CARRINHO

    public function test_can_list_cart(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['price' => 100.00]);

        $this->addToCart($user, $product, 2);

        $response = $this->getJson('/api/cart', $headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'items',
                'cart_total',
            ])
            ->assertJsonPath('cart_total', 200);
    }

    // ADICIONAR AO CARRINHO

    public function test_can_add_product_to_cart(): void
    {
        ['headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['stock' => 10]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.product.id', $product->id);
    }

    public function test_adding_same_product_increments_quantity(): void
    {
        ['headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['stock' => 10]);

        // Primeira adição
        $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 2,
        ], $headers);

        // Segunda adição
        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 3,
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity', 5);
    }

    public function test_cannot_add_more_than_stock(): void
    {
        ['headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['stock' => 3]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 5,
        ], $headers);

        $response->assertStatus(422);
    }

    public function test_cannot_add_inactive_product(): void
    {
        ['headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['is_active' => false]);

        $response = $this->postJson('/api/cart', [
            'product_id' => $product->id,
            'quantity'   => 1,
        ], $headers);

        // O StoreCartRequest valida que o produto deve ser ativo (Rule::exists + is_active)
        $response->assertStatus(422);
    }

    // ATUALIZAR QUANTIDADE
    public function test_can_update_cart_quantity(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['stock' => 10]);
        $cartItem = $this->addToCart($user, $product, 2);

        $response = $this->putJson('/api/cart/' . $cartItem->id, [
            'quantity' => 5,
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.quantity', 5);
    }

    public function test_cannot_update_beyond_stock(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct(['stock' => 3]);
        $cartItem = $this->addToCart($user, $product, 1);

        $response = $this->putJson('/api/cart/' . $cartItem->id, [
            'quantity' => 10,
        ], $headers);

        $response->assertStatus(422);
    }

    public function test_cannot_update_other_users_cart(): void
    {
        ['user' => $userA] = $this->createUser();
        ['headers' => $headersB] = $this->createUser(['email' => 'outro@teste.com']);
        $product = $this->createProduct(['stock' => 10]);

        $cartItem = $this->addToCart($userA, $product, 1);

        $response = $this->putJson('/api/cart/' . $cartItem->id, [
            'quantity' => 5,
        ], $headersB);

        $response->assertStatus(403);
    }

    // REMOVER ITENS
    public function test_can_remove_item(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product = $this->createProduct();
        $cartItem = $this->addToCart($user, $product, 1);

        $response = $this->deleteJson('/api/cart/' . $cartItem->id, [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_cannot_remove_other_users_item(): void
    {
        ['user' => $userA] = $this->createUser();
        ['headers' => $headersB] = $this->createUser(['email' => 'outro@teste.com']);
        $product = $this->createProduct();

        $cartItem = $this->addToCart($userA, $product, 1);

        $response = $this->deleteJson('/api/cart/' . $cartItem->id, [], $headersB);

        $response->assertStatus(403);
        $this->assertDatabaseHas('cart_items', ['id' => $cartItem->id]);
    }

    // LIMPAR CARRINHO
    public function test_can_clear_cart(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $product1 = $this->createProduct(['name' => 'Produto A']);
        $product2 = $this->createProduct(['name' => 'Produto B']);

        $this->addToCart($user, $product1, 1);
        $this->addToCart($user, $product2, 2);

        $response = $this->deleteJson('/api/cart', [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseCount('cart_items', 0);
    }

    // ACESSO
    public function test_guest_cannot_access_cart(): void
    {
        $response = $this->getJson('/api/cart');

        $response->assertStatus(401);
    }
}
