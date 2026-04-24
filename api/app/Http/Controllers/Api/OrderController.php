<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Lista as encomendas do utilizador autenticado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with(['user', 'orderItems', 'shippingMethod'])
            ->latest()
            ->paginate(min($request->integer('per_page', 10), 100));

        return OrderResource::collection($orders);
    }

    /**
     * Detalhe de uma encomenda do utilizador.
     */
    public function show(Request $request, string $orderNumber): JsonResponse|OrderResource
    {
        $order = $request->user()
            ->orders()
            ->with(['user', 'orderItems', 'shippingMethod', 'payments'])
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Encomenda não encontrada.'], 404);
        }

        return new OrderResource($order);
    }

    /**
     * Checkout — cria uma encomenda a partir do carrinho do utilizador.
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        $cartItems = $user->cartItems()->with('product.primaryImage')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'O carrinho está vazio.'], 422);
        }

        $shippingMethod = ShippingMethod::find($validated['shipping_method_id']);

        if (! $shippingMethod || ! $shippingMethod->is_active) {
            return response()->json(['message' => 'O método de envio selecionado não está disponível.'], 422);
        }

        $shippingData = $this->resolveShippingData($user, $validated);

        $subtotal     = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);
        $shippingCost = (float) $shippingMethod->price;

        $countryCode = strtoupper($shippingData['country'] ?? 'PT');
        $taxRateModel = TaxRate::where('country_code', $countryCode)
            ->where('is_active', true)
            ->first();

        $taxPercentage = $taxRateModel ? (float) $taxRateModel->rate : 0.0;
        $taxAmount = round($subtotal * ($taxPercentage / 100), 2);

        $total        = $subtotal + $shippingCost + $taxAmount;

        DB::beginTransaction();
        try {
            // Decremento atómico do stock com verificação — previne overselling
            foreach ($cartItems as $item) {
                $updated = DB::table('products')
                    ->where('id', $item->product->id)
                    ->where('stock', '>=', $item->quantity)
                    ->update(['stock' => DB::raw("stock - {$item->quantity}")]);

                if ($updated === 0) {
                    DB::rollBack();
                    // Buscar stock atualizado para a mensagem de erro
                    $currentStock = DB::table('products')->where('id', $item->product->id)->value('stock');
                    return response()->json([
                        'message' => "Stock insuficiente para '{$item->product->name}'. Apenas existem {$currentStock} unidades disponíveis.",
                    ], 422);
                }
            }

            // Cria a encomenda
            $order = Order::create([
                'user_id'            => $user->id,
                'order_number'       => Order::generateOrderNumber(),
                'status'             => OrderStatus::PENDING,
                'payment_status'     => PaymentStatus::PENDING,

                'shipping_firstname'   => $shippingData['firstname'],
                'shipping_lastname'    => $shippingData['lastname'],
                'shipping_phone'       => $shippingData['phone'] ?? $user->phone,
                'shipping_address_line1' => $shippingData['address_line1'],
                'shipping_address_line2' => $shippingData['address_line2'] ?? null,
                'shipping_city'        => $shippingData['city'],
                'shipping_postal_code' => $shippingData['postal_code'],
                'shipping_country'     => $shippingData['country'] ?? 'PT',

                'shipping_method_id'   => $shippingMethod->id,
                'shipping_carrier'     => $shippingMethod->carrier,
                'estimated_days'       => $shippingMethod->estimated_days,

                'nif'          => $validated['nif'] ?? null,
                'subtotal'     => round($subtotal, 2),
                'shipping_cost' => round($shippingCost, 2),
                'tax_amount'   => $taxAmount,
                'tax_rate'     => $taxPercentage,
                'total'        => round($total, 2),
            ]);

            // Snapshot dos dados do produto
            foreach ($cartItems as $item) {
                $order->orderItems()->create([
                    'product_id'    => $item->product->id,
                    'product_name'  => $item->product->name,
                    'product_image' => $item->product->primaryImage?->url ?? null,
                    'unit_price'    => $item->product->price,
                    'quantity'      => $item->quantity,
                    'item_total'    => round($item->quantity * $item->product->price, 2),
                ]);
            }

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

            $user->cartItems()->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao processar a encomenda.'], 500);
        }

        $order->load(['orderItems', 'shippingMethod', 'payments']);

        return response()->json([
            'message' => 'Encomenda criada com sucesso.',
            'data'    => new OrderResource($order),
        ], 201);
    }

    /**
     * Resolve os dados de envio a partir de um endereço guardado ou de campos manuais.
     */
    private function resolveShippingData($user, array $validated): array
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
