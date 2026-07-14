<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderItem;

use App\Models\Order;
use App\Models\Product;

class OrderItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::with('primaryImage')->get();

        if ($orders->isEmpty() || $products->isEmpty()) {
            OrderItem::factory()->count(25)->create();
            return;
        }

        foreach ($orders as $order) {
            $subtotal = 0;
            $weight = 0;

            // Add 1 to 3 items to each order
            $orderProducts = $products->random(rand(1, 3));
            foreach ($orderProducts as $product) {
                $qty = rand(1, 2);
                $price = $product->price;
                $itemTotal = $price * $qty;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->primaryImage?->url ?? null,
                    'unit_price' => $price,
                    'quantity' => $qty,
                    'item_total' => $itemTotal,
                ]);

                $subtotal += $itemTotal;
                $weight += ($product->weight ?? 0) * $qty;
            }

            $total = $subtotal + (float) $order->shipping_cost;

            $order->update([
                'subtotal' => $subtotal,
                'weight' => round($weight, 3),
                'total' => $total,
            ]);
        }
    }
}
