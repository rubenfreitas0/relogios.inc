<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /**
     * Lista paginada de todas as encomendas com filtros.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->with(['user', 'orderItems', 'shippingMethod'])
            ->when(
                $request->filled('search'),
                fn($q) => $q->where('order_number', 'LIKE', '%' . $request->search . '%')
                    ->orWhereHas('user', fn($u) => $u->where('email', 'LIKE', '%' . $request->search . '%'))
            )
            ->when(
                $request->filled('status'),
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('payment_status'),
                fn($q) => $q->where('payment_status', $request->payment_status)
            )
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return OrderResource::collection($orders);
    }

    /**
     * Detalhe completo de uma encomenda.
     */
    public function show(string $orderNumber): JsonResponse|OrderResource
    {
        $order = Order::with(['user', 'orderItems', 'shippingMethod'])
            ->where('order_number', $orderNumber)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Encomenda não encontrada.'], 404);
        }

        return new OrderResource($order);
    }

    /**
     * Atualizar o estado de uma encomenda (com validações de transição de estado).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, string $orderNumber): JsonResponse|OrderResource
    {
        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            return response()->json(['message' => 'Encomenda não encontrada.'], 404);
        }

        $data = array_filter([
            'status'          => $request->validated('status'),
            'tracking_number' => $request->validated('tracking_number'),
        ], fn($v) => $v !== null);

        // Marcar como pago automaticamente se o estado mudar para 'delivered'
        if ($request->validated('status') === OrderStatus::DELIVERED->value && $order->payment_status !== PaymentStatus::PAID) {
            $data['payment_status'] = PaymentStatus::PAID;
            $data['paid_at']        = now();
        }

        $order->update($data);
        $order->load(['user', 'orderItems', 'shippingMethod']);

        return response()->json([
            'message' => 'Estado da encomenda atualizado com sucesso.',
            'data'    => new OrderResource($order),
        ]);
    }
}
