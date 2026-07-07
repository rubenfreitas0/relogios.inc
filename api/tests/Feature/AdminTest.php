<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class AdminTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    /**
     * Helper: cria admin user e autentica via Sanctum::actingAs.
     */
    private function authenticateAdmin(): User
    {
        $admin = User::factory()->create([
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        return $admin;
    }

    /**
     * Helper: cria uma encomenda para um user via checkout real.
     */
    private function createOrderViaCheckout(): Order
    {
        $userAuth = $this->createUser(['email' => fake()->unique()->safeEmail()]);
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['stock' => 50, 'weight' => 0.5]);

        $this->addToCart($userAuth['user'], $product, 1);
        Sanctum::actingAs($userAuth['user']);
        $this->performCheckout([], $shipping['shippingMethod']->id);

        return Order::latest()->first();
    }

    // ACESSO — PROTEÇÃO DE ROTAS

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        ['headers' => $headers] = $this->createUser(); // role = customer

        $this->getJson('/api/admin/orders', $headers)->assertStatus(403);
        $this->getJson('/api/admin/products', $headers)->assertStatus(403);
        $this->getJson('/api/admin/brands', $headers)->assertStatus(403);
        $this->getJson('/api/admin/categories', $headers)->assertStatus(403);
        $this->getJson('/api/admin/dashboard/stats', $headers)->assertStatus(403);
    }

    // ADMIN — ENCOMENDAS

    public function test_admin_can_list_all_orders(): void
    {
        // Criar encomenda ANTES de autenticar como admin (evita conflito de setupShipping)
        $order1 = $this->createOrderViaCheckout();

        // Segunda encomenda de user diferente (reutilizar shipping existente)
        $userB = $this->createUser(['email' => 'b@teste.com']);
        $product2 = $this->createProduct(['name' => 'Produto B', 'stock' => 50, 'weight' => 0.5]);
        $this->addToCart($userB['user'], $product2, 1);
        $shippingMethod = $order1->shippingMethod;
        Sanctum::actingAs($userB['user']);
        $this->performCheckout([], $shippingMethod->id);

        // Agora autenticar como admin e consultar
        $this->authenticateAdmin();

        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_filter_orders_by_status(): void
    {
        $this->createOrderViaCheckout();
        $this->authenticateAdmin();

        // Order está PENDING por defeito
        $response = $this->getJson('/api/admin/orders?status=pending');
        $response->assertStatus(200)->assertJsonCount(1, 'data');

        $response2 = $this->getJson('/api/admin/orders?status=shipped');
        $response2->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_admin_can_show_order(): void
    {
        $order = $this->createOrderViaCheckout();
        $this->authenticateAdmin();

        $response = $this->getJson('/api/admin/orders/' . $order->order_number);

        $response->assertStatus(200)
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = $this->createOrderViaCheckout();
        $this->authenticateAdmin();

        $this->assertEquals(OrderStatus::PENDING, $order->status);

        // Mudar para shipped (requer tracking)
        $response = $this->patchJson(
            '/api/admin/orders/' . $order->order_number . '/status',
            ['status' => 'shipped', 'tracking_number' => 'CTT123456']
        );

        $response->assertStatus(200);
        $this->assertEquals(OrderStatus::SHIPPED, $order->fresh()->status);
        $this->assertEquals('CTT123456', $order->fresh()->tracking_number);
    }

    public function test_delivered_order_auto_marks_paid(): void
    {
        $order = $this->createOrderViaCheckout();
        $this->authenticateAdmin();

        // Marcar como shipped primeiro (via DB para não depender da API)
        $order->update([
            'status'          => OrderStatus::SHIPPED,
            'tracking_number' => 'CTT789',
        ]);

        // Marcar como delivered via API
        $response = $this->patchJson(
            '/api/admin/orders/' . $order->order_number . '/status',
            ['status' => 'delivered']
        );

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals(OrderStatus::DELIVERED, $order->status);
        $this->assertEquals(PaymentStatus::PAID, $order->payment_status);
    }

    // ADMIN — BRANDS CRUD

    public function test_admin_can_crud_brands(): void
    {
        Storage::fake('public');
        $this->authenticateAdmin();

        // CREATE
        $response = $this->postJson('/api/admin/brands', [
            'name'      => 'Omega',
            'is_active' => true,
            'logo'      => UploadedFile::fake()->image('omega.png'),
        ]);

        $response->assertStatus(201);
        $brand = Brand::first();
        $this->assertEquals('Omega', $brand->name);

        // UPDATE
        $this->putJson('/api/admin/brands/' . $brand->id, [
            'name' => 'Omega Renomeada',
        ])->assertStatus(200);

        $this->assertEquals('Omega Renomeada', $brand->fresh()->name);

        // DELETE (desativa)
        $this->deleteJson('/api/admin/brands/' . $brand->id)
            ->assertStatus(200);

        $this->assertFalse($brand->fresh()->is_active);
    }

    // ADMIN — CATEGORIES (apenas leitura e edição; são fixas, não se criam nem eliminam)

    public function test_admin_can_view_and_update_categories(): void
    {
        $this->authenticateAdmin();

        $category = Category::factory()->create(['name' => 'Desportivo']);

        // READ
        $this->getJson('/api/admin/categories/' . $category->id)
            ->assertStatus(200)
            ->assertJsonPath('data.name', 'Desportivo');

        // UPDATE
        $this->putJson('/api/admin/categories/' . $category->id, [
            'name' => 'Desportivo Premium',
        ])->assertStatus(200);

        $this->assertEquals('Desportivo Premium', $category->fresh()->name);
    }

    public function test_admin_cannot_create_or_delete_categories(): void
    {
        $this->authenticateAdmin();

        $category = Category::factory()->create();

        $this->postJson('/api/admin/categories', ['name' => 'Nova Categoria'])
            ->assertStatus(405);

        $this->deleteJson('/api/admin/categories/' . $category->id)
            ->assertStatus(405);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // ADMIN — PRODUCTS CRUD

    public function test_admin_can_crud_products(): void
    {
        Storage::fake('public');
        $this->authenticateAdmin();
        $brand = Brand::factory()->create(['is_active' => true]);
        $category = Category::factory()->create(['is_active' => true]);

        // CREATE (com imagem para evitar crash no store que espera file upload)
        $response = $this->postJson('/api/admin/products', [
            'name'              => 'Rolex Submariner',
            'brand_id'          => $brand->id,
            'category_id'       => $category->id,
            'gender'            => 'masculino',
            'short_description' => 'Um relógio elegante e desportivo.',
            'description'       => 'Detalhes completos do relógio Rolex Submariner.',
            'price'             => 9999.99,
            'stock'             => 5,
            'weight'            => 0.150,
            'images'            => [UploadedFile::fake()->image('rolex.jpg')],
        ]);

        $response->assertStatus(201);
        $product = Product::first();
        $this->assertEquals('Rolex Submariner', $product->name);

        // UPDATE
        $this->putJson('/api/admin/products/' . $product->id, [
            'price' => 10500.00,
        ])->assertStatus(200);

        $this->assertEquals('10500.00', $product->fresh()->price);

        // DELETE (soft delete)
        $this->deleteJson('/api/admin/products/' . $product->id)
            ->assertStatus(204);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_can_restore_product(): void
    {
        $this->authenticateAdmin();
        $product = $this->createProduct(['name' => 'Restaurável']);

        // Soft delete
        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        // Restore
        $response = $this->postJson('/api/admin/products/' . $product->id . '/restore');

        $response->assertStatus(200);
        $this->assertNotSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_admin_can_update_stock(): void
    {
        $this->authenticateAdmin();
        $product = $this->createProduct(['stock' => 5]);

        $response = $this->patchJson('/api/admin/products/' . $product->id . '/stock', [
            'stock' => 100,
        ]);

        $response->assertStatus(200);
        $this->assertEquals(100, $product->fresh()->stock);
    }

    // ADMIN — DASHBOARD

    public function test_admin_can_get_dashboard_stats(): void
    {
        $this->authenticateAdmin();

        // Criar dados de base
        $this->createProduct(['stock' => 0]); // out of stock
        $this->createProduct(['stock' => 3]); // low stock
        $this->createProduct(['stock' => 50]); // normal

        $response = $this->getJson('/api/admin/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products' => ['total', 'active', 'out_of_stock', 'low_stock'],
                'orders'   => ['today', 'this_month', 'last_month', 'by_status'],
                'revenue'  => ['this_month', 'last_month', 'by_month'],
                'customers',
                'latest_orders',
            ]);
    }
}
