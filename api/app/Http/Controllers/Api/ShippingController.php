<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    /**
     * Calcular opções de envio disponíveis com base no peso do carrinho e localização
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'country' => 'required|string|size:2',
        ]);

        $user = $request->user();
        $countryCode = strtoupper($validated['country']);

        // Buscar itens do carrinho com o produto
        $cartItems = $user->cartItems()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'O carrinho está vazio.',
            ], 422);
        }

        // Calcular peso e subtotal do carrinho
        $totalWeight = 0;
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $totalWeight += ($item->product->weight ?? 0) * $item->quantity;
            $subtotal += ($item->product->price ?? 0) * $item->quantity;
        }

        // Descobrir a Zona de Envio
        $zoneCountry = \App\Models\ShippingZoneCountry::where('country_code', $countryCode)->first();
        $zoneId = $zoneCountry ? $zoneCountry->shipping_zone_id : null;

        // Buscar métodos de envio disponíveis para este peso E zona (ou método global com null)
        $shippingMethods = ShippingMethod::active()
            ->where('min_weight', '<=', $totalWeight)
            ->where('max_weight', '>=', $totalWeight)
            ->where(function($query) use ($zoneId) {
                $query->where('shipping_zone_id', $zoneId)
                      ->orWhereNull('shipping_zone_id'); // Fallback para Global
            })
            ->orderBy('price')
            ->get();

        if ($shippingMethods->isEmpty()) {
            return response()->json([
                'message' => 'Não existem métodos de envio disponíveis para este país e peso total.',
                'total_weight' => round($totalWeight, 3),
            ], 422);
        }

        // Buscar a Taxa de IVA para o país destino
        $taxRateModel = \App\Models\TaxRate::where('country_code', $countryCode)
            ->where('is_active', true)
            ->first();

        $taxPercentage = $taxRateModel ? (float) $taxRateModel->rate : 0.0;
        $taxAmount = round($subtotal * ($taxPercentage / 100), 2);

        return response()->json([
            'subtotal'        => round($subtotal, 2),
            'total_weight'    => round($totalWeight, 3),
            'tax_rate_name'   => $taxRateModel ? $taxRateModel->name : 'N/A',
            'tax_rate_percent'=> $taxPercentage,
            'tax_amount'      => $taxAmount,
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
