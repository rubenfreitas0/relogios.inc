<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Lista as encomendas do utilizador autenticado.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()
            ->orders()
            ->with(['orderItems', 'shippingMethod'])
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return OrderResource::collection($orders);
    }

    /**
     * Detalhe de uma encomenda do utilizador (verificação de ownership).
     */
    public function show(Request $request, string $orderNumber): JsonResponse|OrderResource
    {
        $order = $request->user()
            ->orders()
            ->with(['orderItems', 'shippingMethod', 'payments'])
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

        // 1. Verificar se o carrinho tem items
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'O carrinho está vazio.'], 422);
        }

        // 2. Obter e validar o método de envio
        $shippingMethod = ShippingMethod::find($validated['shipping_method_id']);

        if (! $shippingMethod || ! $shippingMethod->is_active) {
            return response()->json(['message' => 'O método de envio selecionado não está disponível.'], 422);
        }

        // 3. Verificar stock de todos os produtos antes de criar a encomenda
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'message' => "Stock insuficiente para '{$item->product->name}'. Apenas existem {$item->product->stock} unidades disponíveis.",
                ], 422);
            }
        }

        // 4. Resolver dados de envio (endereço guardado ou manual)
        $shippingData = $this->resolveShippingData($user, $validated);

        // 5. Calcular valores
        $subtotal     = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);
        $shippingCost = (float) $shippingMethod->price;
        $total        = $subtotal + $shippingCost;

        // 6. Criar a encomenda
        $order = Order::create([
            'user_id'            => $user->id,
            'order_number'       => $this->generateOrderNumber(),
            'status'             => 'pending',
            'payment_status'     => 'pending',

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
            'shipping_cost'=> round($shippingCost, 2),
            'total'        => round($total, 2),
        ]);

        // 7. Criar os itens da encomenda (snapshot dos dados do produto)
        foreach ($cartItems as $item) {
            $order->orderItems()->create([
                'product_id'    => $item->product->id,
                'product_name'  => $item->product->name,
                'product_image' => $item->product->primaryImage?->url ?? null,
                'unit_price'    => $item->product->price,
                'quantity'      => $item->quantity,
                'item_total'    => round($item->quantity * $item->product->price, 2),
            ]);

            // 8. Decrementar stock
            $item->product->decrement('stock', $item->quantity);
        }

        // 9. Processar o Pagamento
        $paymentData = null;
        if ($validated['payment_method'] === 'multibanco') {
            $paymentData = [
                'entity'     => '12345',
                'reference'  => rand(100000000, 999999999), // Mock
                'expires_at' => now()->addDays(3)->toDateTimeString(),
            ];
        } elseif ($validated['payment_method'] === 'mbway') {
            $paymentData = [
                'phone' => $validated['payment_phone'],
            ];
        }

        $order->payments()->create([
            'method'       => $validated['payment_method'],
            'amount'       => round($total, 2),
            'currency'     => 'EUR',
            'status'       => 'pending',
            'payment_data' => $paymentData,
        ]);

        // 10. Esvaziar o carrinho
        $user->cartItems()->delete();

        // 11. Carregar relações para a resposta
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
                'address_line1'=> $address->address_line1,
                'address_line2'=> $address->address_line2,
                'city'         => $address->city,
                'postal_code'  => $address->postal_code,
                'country'      => $address->country,
            ];
        }

        return [
            'firstname'    => $validated['firstname'],
            'lastname'     => $validated['lastname'],
            'phone'        => $validated['phone'] ?? null,
            'address_line1'=> $validated['address_line1'],
            'address_line2'=> $validated['address_line2'] ?? null,
            'city'         => $validated['city'],
            'postal_code'  => $validated['postal_code'],
            'country'      => $validated['country'] ?? 'PT',
        ];
    }

    /**
     * Gera um número de encomenda único no formato RL-YYYYMMDD-XXXXX.
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'RL-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
