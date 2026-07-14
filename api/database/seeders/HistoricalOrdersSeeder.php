<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Gera encomendas fictícias distribuídas pelos últimos meses, com uma mistura
 * realista de estados de pagamento, para o gráfico de faturação do dashboard
 * do backoffice não aparecer vazio (só existem dados reais para o mês atual).
 */
class HistoricalOrdersSeeder extends Seeder
{
    private const PAYMENT_METHODS = ['mbway', 'multibanco', 'credit_card', 'apple_pay', 'google_pay'];

    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::where('is_active', true)->with('primaryImage')->get();
        $shippingMethods = ShippingMethod::all();

        if ($customers->isEmpty() || $products->isEmpty() || $shippingMethods->isEmpty()) {
            return;
        }

        for ($monthsAgo = 6; $monthsAgo >= 1; $monthsAgo--) {
            $monthStart = now()->subMonths($monthsAgo)->startOfMonth();
            $ordersThisMonth = rand(4, 10);

            for ($i = 0; $i < $ordersThisMonth; $i++) {
                $createdAt = $monthStart->copy()
                    ->addDays(rand(0, 26))
                    ->addHours(rand(8, 20))
                    ->addMinutes(rand(0, 59));

                $this->createHistoricalOrder($customers, $products, $shippingMethods, $createdAt);
            }
        }
    }

    private function createHistoricalOrder($customers, $products, $shippingMethods, \Carbon\Carbon $createdAt): void
    {
        // 75% das encomendas antigas ficam pagas e entregues; as restantes
        // simulam falhas/cancelamentos, para dar variedade aos estados.
        $isPaid = fake()->boolean(75);
        $paymentStatus = $isPaid
            ? PaymentStatus::PAID
            : fake()->randomElement([PaymentStatus::PENDING, PaymentStatus::FAILED]);
        $status = match (true) {
            $isPaid => fake()->randomElement([OrderStatus::DELIVERED, OrderStatus::SHIPPED, OrderStatus::PROCESSING]),
            $paymentStatus === PaymentStatus::FAILED => OrderStatus::CANCELLED,
            default => OrderStatus::PENDING,
        };

        $shippingMethod = $shippingMethods->random();
        $orderProducts = $products->random(min(3, $products->count()));
        if (! $orderProducts instanceof \Illuminate\Support\Collection) {
            $orderProducts = collect([$orderProducts]);
        }

        $order = Order::create([
            'user_id' => $customers->random()->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => $status,
            'payment_status' => $paymentStatus,

            'shipping_firstname' => fake()->firstName(),
            'shipping_lastname' => fake()->lastName(),
            'shipping_phone' => fake()->numerify('9########'),
            'shipping_address_line1' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_postal_code' => fake()->numerify('####-###'),
            'shipping_country' => 'PT',

            'shipping_method_id' => $shippingMethod->id,
            'shipping_carrier' => $shippingMethod->carrier,
            'estimated_days' => $shippingMethod->estimated_days,

            'subtotal' => 0,
            'shipping_cost' => $shippingMethod->price,
            'total' => 0,
            'paid_at' => $isPaid ? $createdAt : null,
        ]);

        $subtotal = 0;
        $weight = 0;

        foreach ($orderProducts as $product) {
            $qty = rand(1, 2);
            $price = $product->discount_price ?? $product->price;
            $itemTotal = round($price * $qty, 2);
            $subtotal += $itemTotal;
            $weight += ($product->weight ?? 0) * $qty;

            $order->orderItems()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->primaryImage?->url,
                'unit_price' => $price,
                'quantity' => $qty,
                'item_total' => $itemTotal,
            ]);
        }

        $total = round($subtotal + (float) $shippingMethod->price, 2);
        $order->update([
            'subtotal' => round($subtotal, 2),
            'weight' => round($weight, 3),
            'total' => $total,
        ]);

        if ($paymentStatus !== PaymentStatus::PENDING) {
            $order->payments()->create([
                'method' => fake()->randomElement(self::PAYMENT_METHODS),
                'transaction_id' => fake()->uuid(),
                'amount' => $total,
                'currency' => 'EUR',
                'status' => $paymentStatus->value,
                'paid_at' => $isPaid ? $createdAt : null,
            ]);
        }

        // Retroceder created_at/updated_at (e dos registos relacionados) para o
        // mês pretendido — por omissão ficariam todos com a data de hoje.
        $order->orderItems()->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
        $order->payments()->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
        $order->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();
    }
}
