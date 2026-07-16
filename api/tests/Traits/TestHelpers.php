<?php

namespace Tests\Traits;

use App\Models\Brand;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use App\Models\User;

/**
 * Helpers reutilizáveis para os testes de integração.
 * Centraliza a criação de entidades para evitar repetição de setup.
 */
trait TestHelpers
{
    /**
     * Cria um user normal (verificado) e devolve [user, token, headers].
     */
    protected function createUser(array $overrides = []): array
    {
        $user = User::factory()->create(array_merge([
            'role'              => 'customer',
            'is_active'         => true,
            'email_verified_at' => now(),
        ], $overrides));

        $token = $user->createToken('test_token')->plainTextToken;

        return [
            'user'    => $user,
            'token'   => $token,
            'headers' => ['Authorization' => "Bearer {$token}"],
        ];
    }

    /**
     * Cria um user admin (verificado) e devolve [user, token, headers].
     */
    protected function createAdmin(array $overrides = []): array
    {
        return $this->createUser(array_merge([
            'role' => 'admin',
        ], $overrides));
    }

    /**
     * Cria um produto ativo com brand, category e imagem primária.
     */
    protected function createProduct(array $overrides = []): Product
    {
        // Categorias podem ser passadas via 'categories' (array de ids/models)
        // ou 'category_id' (compatibilidade) — ambos são associados via pivot.
        $categories = $overrides['categories'] ?? null;
        if (isset($overrides['category_id'])) {
            $categories = array_merge((array) ($categories ?? []), [$overrides['category_id']]);
        }
        unset($overrides['categories'], $overrides['category_id']);

        $product = Product::factory()->create(array_merge([
            'is_active' => true,
            'stock'     => 10,
            'price'     => 199.99,
            'weight'    => 0.5,
        ], $overrides));

        if (!empty($categories)) {
            $ids = collect($categories)->map(
                fn($c) => is_object($c) ? $c->id : $c
            )->all();
            $product->categories()->sync($ids);
        }

        ProductImage::factory()->create([
            'product_id' => $product->id,
            'is_primary' => true,
        ]);

        return $product->fresh();
    }

    /**
     * Configura a infraestrutura de envio:
     * Zona "Portugal Continental" + País PT + Método ativo.
     *
     * Devolve [zone, zoneCountry, shippingMethod].
     */
    protected function setupShipping(array $methodOverrides = []): array
    {
        $zone = ShippingZone::factory()->create([
            'name'      => 'Portugal Continental',
            'is_active' => true,
        ]);

        $zoneCountry = ShippingZoneCountry::factory()->create([
            'shipping_zone_id' => $zone->id,
            'country_code'     => 'PT',
        ]);

        $shippingMethod = ShippingMethod::factory()->create(array_merge([
            'shipping_zone_id' => $zone->id,
            'name'             => 'CTT Normal',
            'carrier'          => 'CTT',
            'price'            => 5.00,
            'min_weight'       => 0.000,
            'max_weight'       => 30.000,
            'estimated_days'   => '2-3 dias úteis',
            'is_active'        => true,
        ], $methodOverrides));

        return compact('zone', 'zoneCountry', 'shippingMethod');
    }

    /**
     * Adiciona um produto ao carrinho de um user.
     */
    protected function addToCart(User $user, Product $product, int $quantity = 1): CartItem
    {
        return CartItem::factory()->create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
            'quantity'   => $quantity,
        ]);
    }

    /**
     * Executa um checkout completo via API e devolve a response.
     */
    protected function performCheckout(array $headers, int $shippingMethodId, array $overrides = [])
    {
        $payload = array_merge([
            'shipping_method_id' => $shippingMethodId,
            'payment_method'     => 'multibanco',
            'firstname'          => 'João',
            'lastname'           => 'Silva',
            'phone'              => '912345678',
            'address_line1'      => 'Rua de Teste 123',
            'city'               => 'Lisboa',
            'postal_code'        => '1000-001',
            'country'            => 'PT',
        ], $overrides);

        return $this->postJson('/api/orders', $payload, $headers);
    }
}
