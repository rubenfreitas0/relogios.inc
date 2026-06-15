<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class CatalogTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // PRODUCTS — LISTING
    public function test_can_list_active_products(): void
    {
        $this->createProduct(['name' => 'Rolex Submariner']);
        $this->createProduct(['name' => 'Omega Seamaster']);

        $response = $this->getJson('/api/catalog/products');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.is_active', true)
            ->assertJsonPath('data.1.is_active', true);
    }

    public function test_inactive_products_are_hidden(): void
    {
        $this->createProduct(['name' => 'Visível', 'is_active' => true]);
        $this->createProduct(['name' => 'Escondido', 'is_active' => false]);

        $response = $this->getJson('/api/catalog/products');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Visível');
    }

    public function test_can_filter_by_category(): void
    {
        $categoryA = Category::factory()->create(['name' => 'Mergulho', 'is_active' => true]);
        $categoryB = Category::factory()->create(['name' => 'Desportivo', 'is_active' => true]);

        $this->createProduct(['name' => 'Sub', 'category_id' => $categoryA->id]);
        $this->createProduct(['name' => 'Speedmaster', 'category_id' => $categoryB->id]);

        $response = $this->getJson('/api/catalog/products?category=' . $categoryA->slug);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Sub');
    }

    public function test_can_filter_by_brand(): void
    {
        $brandA = Brand::factory()->create(['name' => 'Rolex', 'is_active' => true]);
        $brandB = Brand::factory()->create(['name' => 'Omega', 'is_active' => true]);

        $this->createProduct(['name' => 'Submariner', 'brand_id' => $brandA->id]);
        $this->createProduct(['name' => 'Seamaster', 'brand_id' => $brandB->id]);

        $response = $this->getJson('/api/catalog/products?brand=' . $brandA->slug);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Submariner');
    }

    public function test_can_filter_by_price_range(): void
    {
        $this->createProduct(['name' => 'Barato', 'price' => 50.00]);
        $this->createProduct(['name' => 'Médio', 'price' => 150.00]);
        $this->createProduct(['name' => 'Caro', 'price' => 500.00]);

        $response = $this->getJson('/api/catalog/products?min_price=100&max_price=200');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Médio');
    }

    public function test_can_search_by_name(): void
    {
        $this->createProduct(['name' => 'Rolex Submariner Date']);
        $this->createProduct(['name' => 'Casio G-Shock']);

        $response = $this->getJson('/api/catalog/products?search=submariner');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Rolex Submariner Date');
    }

    public function test_can_sort_products(): void
    {
        $this->createProduct(['name' => 'Caro', 'price' => 500.00]);
        $this->createProduct(['name' => 'Barato', 'price' => 50.00]);

        $response = $this->getJson('/api/catalog/products?sort=price_asc');

        $response->assertStatus(200);

        $prices = collect($response->json('data'))->pluck('price')->all();
        $this->assertEquals(['50.00', '500.00'], $prices);
    }

    public function test_can_list_featured_products(): void
    {
        $this->createProduct(['name' => 'Destaque', 'is_featured' => true]);
        $this->createProduct(['name' => 'Normal', 'is_featured' => false]);

        $response = $this->getJson('/api/catalog/products/featured');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Destaque');
    }

    // PRODUCTS — DETAIL & RELATED

    public function test_can_show_product_detail(): void
    {
        $product = $this->createProduct(['name' => 'Rolex Submariner']);

        $response = $this->getJson('/api/catalog/products/' . $product->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Rolex Submariner')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'price', 'brand', 'category'],
            ]);
    }

    public function test_show_returns_404_for_inactive(): void
    {
        $product = $this->createProduct(['name' => 'Inativo', 'is_active' => false]);

        $response = $this->getJson('/api/catalog/products/' . $product->slug);

        $response->assertStatus(404);
    }

    public function test_can_list_related_products(): void
    {
        $category = Category::factory()->create(['name' => 'Mergulho', 'is_active' => true]);

        $main = $this->createProduct(['name' => 'Submariner', 'category_id' => $category->id]);
        $related1 = $this->createProduct(['name' => 'Sea-Dweller', 'category_id' => $category->id]);
        $related2 = $this->createProduct(['name' => 'Aqua Terra', 'category_id' => $category->id]);

        // Produto de outra categoria — não deve aparecer
        $other = Category::factory()->create(['name' => 'Piloto', 'is_active' => true]);
        $this->createProduct(['name' => 'Navitimer', 'category_id' => $other->id]);

        $response = $this->getJson('/api/catalog/products/' . $main->slug . '/related');

        $response->assertStatus(200);

        $relatedIds = collect($response->json('data'))->pluck('id')->all();

        // O próprio produto NÃO deve aparecer nos related
        $this->assertNotContains($main->id, $relatedIds);

        // Deve conter os da mesma categoria
        $this->assertContains($related1->id, $relatedIds);
        $this->assertContains($related2->id, $relatedIds);

        // Não deve conter o de outra categoria
        $navitimerId = Product::where('name', 'Navitimer')->first()->id;
        $this->assertNotContains($navitimerId, $relatedIds);
    }

    // BRANDS
    public function test_can_list_brands(): void
    {
        Brand::factory()->create(['name' => 'Rolex', 'is_active' => true]);
        Brand::factory()->create(['name' => 'Omega', 'is_active' => true]);
        Brand::factory()->create(['name' => 'Inativa', 'is_active' => false]);

        $response = $this->getJson('/api/catalog/brands');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_show_brand(): void
    {
        $brand = Brand::factory()->create(['name' => 'Rolex', 'is_active' => true]);

        $response = $this->getJson('/api/catalog/brands/' . $brand->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $brand->id)
            ->assertJsonPath('data.name', 'Rolex');
    }

    // CATEGORIES
    public function test_can_list_categories(): void
    {
        Category::factory()->create(['name' => 'Mergulho', 'is_active' => true]);
        Category::factory()->create(['name' => 'Desportivo', 'is_active' => true]);
        Category::factory()->create(['name' => 'Inativa', 'is_active' => false]);

        $response = $this->getJson('/api/catalog/categories');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_show_category(): void
    {
        $category = Category::factory()->create(['name' => 'Mergulho', 'is_active' => true]);

        $response = $this->getJson('/api/catalog/categories/' . $category->slug);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $category->id)
            ->assertJsonPath('data.name', 'Mergulho');
    }
}
