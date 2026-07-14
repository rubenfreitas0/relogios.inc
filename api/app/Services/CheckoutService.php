<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\CheckoutException;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Serviço responsável por todo o fluxo de checkout:
 * validação do carrinho, cálculo de valores, decremento de stock com locks,
 * criação da encomenda, items, pagamento e limpeza do carrinho.
 *
 * Toda a operação corre dentro de uma transação com locks pessimistas
 * para prevenir race conditions e overselling.
 */
class CheckoutService
{
    public function __construct(
        private ShippingZoneResolver $zoneResolver,
    ) {}

    /**
     * Processa o checkout completo.
     *
     * @throws CheckoutException
     */
    public function process(User $user, array $validated): Order
    {
        return DB::transaction(function () use ($user, $validated) {

            // ─── 0. Validar se o email está verificado ───
            if (! $user->hasVerifiedEmail()) {
                throw new CheckoutException('Por favor, verifica o teu email antes de efetuar a primeira compra.', 403);
            }

            // ─── 1. Carregar carrinho com lock nos produtos ───
            $cartItems = $user->cartItems()
                ->with([
                    'product' => fn($q) => $q->lockForUpdate(),
                    'product.primaryImage',
                ])
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw new CheckoutException('O carrinho está vazio.');
            }

            // ─── 2. Validar método de envio ───
            $shippingMethod = ShippingMethod::find($validated['shipping_method_id']);

            if (! $shippingMethod || ! $shippingMethod->is_active) {
                throw new CheckoutException('O método de envio selecionado não está disponível.');
            }

            // ─── 3. Resolver dados de envio ───
            $shippingData = $this->resolveShippingData($user, $validated);

            // ─── 4. Calcular subtotal e peso ───
            $subtotal    = 0;
            $totalWeight = 0;

            foreach ($cartItems as $item) {
                if (! $item->product) {
                    throw new CheckoutException('Um ou mais produtos no seu carrinho já não estão disponíveis.');
                }

                if (! $item->product->is_active) {
                    throw new CheckoutException("O produto '{$item->product->name}' já não está disponível.");
                }

                $itemPrice = $item->product->discount_price ?? $item->product->price;
                $subtotal    += $item->quantity * $itemPrice;
                $totalWeight += ($item->product->weight ?? 0) * $item->quantity;
            }

            // ─── 5. Validar zona de envio ───
            $countryCode = strtoupper($shippingData['country'] ?? 'PT');
            $postalCode  = $shippingData['postal_code'] ?? null;
            $expectedZoneId = $this->zoneResolver->resolve($countryCode, $postalCode);

            if ($shippingMethod->shipping_zone_id !== null) {
                if ($expectedZoneId === null || $shippingMethod->shipping_zone_id !== $expectedZoneId) {
                    throw new CheckoutException(
                        'O método de envio selecionado não está disponível para o país de destino.'
                    );
                }
            }

            // ─── 6. Validar peso ───
            if (! $shippingMethod->supportsWeight($totalWeight)) {
                throw new CheckoutException(
                    'O peso total da encomenda não é compatível com o método de envio selecionado.'
                );
            }

            // ─── 7. Calcular total ───
            $shippingCost = (float) $shippingMethod->price;
            $total        = $subtotal + $shippingCost;

            // ─── 8. Decrementar stock com verificação atómica ───
            foreach ($cartItems as $item) {
                $updated = DB::table('products')
                    ->where('id', $item->product->id)
                    ->where('stock', '>=', $item->quantity)
                    ->decrement('stock', $item->quantity);

                if ($updated === 0) {
                    $currentStock = DB::table('products')
                        ->where('id', $item->product->id)
                        ->value('stock');

                    throw new CheckoutException(
                        "Stock insuficiente para '{$item->product->name}'. Apenas existem {$currentStock} unidades disponíveis."
                    );
                }
            }

            // ─── 9. Criar encomenda ───
            $order = Order::create([
                'user_id'            => $user->id,
                'order_number'       => Order::generateOrderNumber(),
                'status'             => OrderStatus::PENDING,
                'payment_status'     => PaymentStatus::PENDING,

                'shipping_firstname'     => $shippingData['firstname'],
                'shipping_lastname'      => $shippingData['lastname'],
                'shipping_phone'         => $shippingData['phone'] ?? $user->phone,
                'shipping_address_line1' => $shippingData['address_line1'],
                'shipping_address_line2' => $shippingData['address_line2'] ?? null,
                'shipping_city'          => $shippingData['city'],
                'shipping_postal_code'   => $shippingData['postal_code'],
                'shipping_country'       => $shippingData['country'] ?? 'PT',

                'shipping_method_id' => $shippingMethod->id,
                'shipping_carrier'   => $shippingMethod->carrier,
                'estimated_days'     => $shippingMethod->estimated_days,
                'weight'             => round($totalWeight, 3),

                'nif'           => $validated['nif'] ?? null,
                'subtotal'      => round($subtotal, 2),
                'shipping_cost' => round($shippingCost, 2),
                'total'         => round($total, 2),
                'notes'         => $validated['notes'] ?? null,
            ]);

            // ─── 10. Snapshot dos items (bulk insert) ───
            $orderItemsData = [];
            foreach ($cartItems as $item) {
                $itemPrice = $item->product->discount_price ?? $item->product->price;
                $orderItemsData[] = [
                    'order_id'      => $order->id,
                    'product_id'    => $item->product->id,
                    'product_name'  => $item->product->name,
                    'product_image' => $item->product->primaryImage?->url ?? null,
                    'unit_price'    => $itemPrice,
                    'quantity'      => $item->quantity,
                    'item_total'    => round($item->quantity * $itemPrice, 2),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
            $order->orderItems()->insert($orderItemsData);

            // ─── 11. Criar pagamento ───
            $paymentData = null;
            if ($validated['payment_method'] === PaymentMethod::MULTIBANCO->value) {
                $paymentData = [
                    'entity'     => '12345',
                    'reference'  => rand(100000000, 999999999),
                    'expires_at' => now()->addDays(3)->toDateTimeString(),
                ];
            } elseif ($validated['payment_method'] === PaymentMethod::MBWAY->value) {
                $paymentData = [
                    'phone' => $validated['payment_phone'],
                ];
            }

            $order->payments()->create([
                'method'       => $validated['payment_method'],
                'amount'       => round($total, 2),
                'currency'     => 'EUR',
                'status'       => PaymentStatus::PENDING,
                'payment_data' => $paymentData,
            ]);

            // ─── 12. Limpar carrinho ───
            $user->cartItems()->delete();

            return $order;
        });
    }

    /**
     * Resolve os dados de envio a partir de um endereço guardado ou de campos manuais.
     */
    private function resolveShippingData(User $user, array $validated): array
    {
        if (! empty($validated['address_id'])) {
            $address = $user->addresses()->findOrFail($validated['address_id']);

            return [
                'firstname'    => $address->firstname,
                'lastname'     => $address->lastname,
                'phone'        => $address->phone,
                'address_line1' => $address->address_line1,
                'address_line2' => $address->address_line2,
                'city'         => $address->city,
                'postal_code'  => $address->postal_code,
                'country'      => $address->country,
            ];
        }

        return [
            'firstname'    => $validated['firstname'],
            'lastname'     => $validated['lastname'],
            'phone'        => $validated['phone'] ?? null,
            'address_line1' => $validated['address_line1'],
            'address_line2' => $validated['address_line2'] ?? null,
            'city'         => $validated['city'],
            'postal_code'  => $validated['postal_code'],
            'country'      => $validated['country'] ?? 'PT',
        ];
    }
}
