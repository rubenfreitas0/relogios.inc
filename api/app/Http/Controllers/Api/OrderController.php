<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

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
     * Toda a lógica de negócio está delegada ao CheckoutService.
     */
    public function store(CheckoutRequest $request): JsonResponse
    {
        $user      = $request->user();
        $validated = $request->validated();

        try {
            $order = $this->checkoutService->process($user, $validated);
        } catch (CheckoutException $e) {
            return response()->json(['message' => $e->getMessage()], $e->httpStatus);
        }

        $order->load(['orderItems', 'shippingMethod', 'payments']);

        return response()->json([
            'message' => 'Encomenda criada com sucesso.',
            'data'    => new OrderResource($order),
        ], 201);
    }
}
