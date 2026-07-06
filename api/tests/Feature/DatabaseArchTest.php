<?php
 
namespace Tests\Feature;
 
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Exception;
 
class DatabaseArchTest extends TestCase
{
    use RefreshDatabase;
 
    /**
     * Test pgSQL trigger tg_log_price_history logs price edits to product_price_logs.
     */
    public function test_tg_log_price_history_logs_price_changes(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('PostgreSQL-specific triggers are not supported in SQLite.');
        }
 
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'price' => 100.00,
            'discount_price' => null,
        ]);
 
        // 1. Change regular price
        $product->price = 120.00;
        $product->save();
 
        $this->assertDatabaseHas('product_price_logs', [
            'product_id' => $product->id,
            'old_price' => 100.00,
            'new_price' => 120.00,
            'old_discount_price' => null,
            'new_discount_price' => null,
        ]);
 
        // 2. Change discount price
        $product->discount_price = 90.00;
        $product->save();
 
        $this->assertDatabaseHas('product_price_logs', [
            'product_id' => $product->id,
            'old_price' => 120.00,
            'new_price' => 120.00,
            'old_discount_price' => null,
            'new_discount_price' => 90.00,
        ]);
    }
 
    /**
     * Test SQL functions: fn_calculate_order_totals and fn_apply_batch_discount.
     */
    public function test_sql_functions(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('PostgreSQL-specific functions are not supported in SQLite.');
        }
 
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $user = User::factory()->create();
        
        $product1 = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'price' => 100.00,
        ]);
        $product2 = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'price' => 50.00,
        ]);
 
        // 1. Test fn_apply_batch_discount function
        // Apply 20% discount to category products
        $updatedCount = DB::selectOne("SELECT fn_apply_batch_discount(?, ?)", [$category->id, 20]);
        $this->assertEquals(2, $updatedCount->fn_apply_batch_discount);
 
        $this->assertEquals(80.00, $product1->refresh()->discount_price);
        $this->assertEquals(40.00, $product2->refresh()->discount_price);
 
        // 2. Test fn_calculate_order_totals
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'tax_rate' => 23.00,
            'shipping_cost' => 10.00,
            'subtotal' => 0.00,
            'tax_amount' => 0.00,
            'total' => 0.00,
        ]);
 
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'unit_price' => 80.00,
            'quantity' => 1,
            'item_total' => 80.00,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'unit_price' => 40.00,
            'quantity' => 2,
            'item_total' => 80.00,
        ]);
 
        // Run SQL function to calculate totals
        DB::statement("SELECT fn_calculate_order_totals(?)", [$order->id]);
 
        $order->refresh();
 
        $this->assertEquals(160.00, $order->subtotal);
        $this->assertEquals(36.80, $order->tax_amount);
        $this->assertEquals(206.80, $order->total);
    }
 
    /**
     * Test SQL database views.
     */
    public function test_database_views(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->markTestSkipped('PostgreSQL-specific views are not supported in SQLite.');
        }
 
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $user = User::factory()->create();
        
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'stock' => 0,
            'price' => 100.00,
            'discount_price' => 80.00,
        ]);
 
        // Test vw_product_stock_status
        $viewProduct = DB::table('vw_product_stock_status')->where('product_id', $product->id)->first();
        $this->assertEquals('OUT_OF_STOCK', $viewProduct->stock_status);
        $this->assertEquals(80.00, $viewProduct->active_price);
 
        // Create an order to verify revenue views
        $zone = ShippingZone::factory()->create();
        $shippingMethod = ShippingMethod::factory()->create(['shipping_zone_id' => $zone->id]);
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => PaymentStatus::PAID,
            'status' => OrderStatus::PROCESSING,
            'shipping_country' => 'PT',
            'shipping_method_id' => $shippingMethod->id,
            'total' => 250.00,
            'subtotal' => 200.00,
            'shipping_cost' => 10.00,
            'tax_amount' => 40.00,
            'created_at' => now(),
        ]);
 
        // Test vw_revenue_by_region
        $regionStats = DB::table('vw_revenue_by_region')->where('country', 'PT')->first();
        $this->assertEquals(250.00, $regionStats->total_revenue);
 
        // Test vw_customer_lifetime_value
        $clvStats = DB::table('vw_customer_lifetime_value')->where('user_id', $user->id)->first();
        $this->assertEquals(250.00, $clvStats->total_spent);
        $this->assertEquals(1, $clvStats->total_orders);
 
        // Test vw_monthly_revenue_report
        $monthlyStats = DB::table('vw_monthly_revenue_report')
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();
        $this->assertEquals(250.00, $monthlyStats->total_revenue);
    }
 
    /**
     * Test Laravel OrderObserver handles stock deduction cleanly and avoids double-triggering.
     */
    public function test_order_observer_stock_deduction(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $user = User::factory()->create();
        
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'stock' => 10,
        ]);
 
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
        ]);
 
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
 
        // 1. Check stock is NOT decremented yet
        $this->assertEquals(10, $product->refresh()->stock);
 
        // 2. Transition to PAID (should trigger OrderObserver stock deduction)
        $order->payment_status = PaymentStatus::PAID;
        $order->save();
 
        $this->assertEquals(7, $product->refresh()->stock);
 
        // 3. Double-trigger protection: save order again (e.g. update tracking number)
        $order->tracking_number = 'TRACK123';
        $order->save();
 
        // Stock should remain 7
        $this->assertEquals(7, $product->refresh()->stock);
    }
 
    /**
     * Test Laravel OrderObserver aborts/rolls back if stock is insufficient.
     */
    public function test_order_observer_throws_exception_on_insufficient_stock(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $user = User::factory()->create();
        
        $product = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'stock' => 2,
        ]);
 
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => OrderStatus::PENDING,
            'payment_status' => PaymentStatus::PENDING,
        ]);
 
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 5, // Exceeds stock (2)
        ]);
 
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Stock insuficiente');
 
        $order->payment_status = PaymentStatus::PAID;
        $order->save();
    }
 
    /**
     * Test Laravel OrderItemObserver recalculates weight.
     */
    public function test_order_item_observer_recalculates_weight(): void
    {
        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $user = User::factory()->create();
        
        $product1 = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'weight' => 1.500, // 1.5 kg
        ]);
        $product2 = Product::factory()->create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'weight' => 0.250, // 250 g
        ]);
 
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'weight' => 0,
        ]);
 
        // 1. Add item 1 (quantity 2 -> total weight = 3.0 kg)
        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
 
        $this->assertEquals(3.000, $order->refresh()->weight);
 
        // 2. Add item 2 (quantity 3 -> total weight = 3.0 + 0.75 = 3.75 kg)
        $item2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
 
        $this->assertEquals(3.750, $order->refresh()->weight);
 
        // 3. Delete item 1 (weight should go back to 0.75 kg)
        $item1->delete();
 
        $this->assertEquals(0.750, $order->refresh()->weight);
    }
}
