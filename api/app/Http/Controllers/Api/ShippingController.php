<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * Calcular opções de envio disponíveis com base no peso do carrinho
     */
    public function calculate(Request $request)
    {
        $user = $request->user();

        // Buscar itens do carrinho com o produto
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'O carrinho está vazio.',
            ], 422);
        }

        // Calcular peso total do carrinho
        $totalWeight = $cartItems->sum(function ($item) {
            return ($item->product->weight ?? 0) * $item->quantity;
        });

        // Buscar métodos de envio disponíveis para este peso
        $shippingMethods = ShippingMethod::active()
            ->where('min_weight', '<=', $totalWeight)
            ->where('max_weight', '>=', $totalWeight)
            ->orderBy('price')
            ->get();

        if ($shippingMethods->isEmpty()) {
            return response()->json([
                'message' => 'Não existem métodos de envio disponíveis para o peso total do carrinho.',
                'total_weight' => round($totalWeight, 3),
            ], 422);
        }

        return response()->json([
            'total_weight'    => round($totalWeight, 3),
            'shipping_methods' => $shippingMethods->map(fn($method) => [
                'id'             => $method->id,
                'name'           => $method->name,
                'carrier'        => $method->carrier,
                'price'          => $method->price,
                'estimated_days' => $method->estimated_days,
            ]),
        ]);
    }

    /**
     * Listar todos os métodos de envio ativos (público)
     */
    public function index()
    {
        $methods = ShippingMethod::active()
            ->orderBy('price')
            ->get()
            ->map(fn($method) => [
                'id'             => $method->id,
                'name'           => $method->name,
                'carrier'        => $method->carrier,
                'price'          => $method->price,
                'min_weight'     => $method->min_weight,
                'max_weight'     => $method->max_weight,
                'estimated_days' => $method->estimated_days,
            ]);

        return response()->json($methods);
    }
}
