<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingMethodRequest;
use App\Http\Requests\Admin\UpdateShippingMethodRequest;
use App\Http\Resources\ShippingMethodResource;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShippingMethodController extends Controller
{
    /**
     * Listagem paginada de métodos de envio.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $methods = ShippingMethod::query()
            ->when(
                $request->filled('search'),
                fn($q) => $q->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('carrier', 'LIKE', '%' . $request->search . '%')
            )
            ->when(
                $request->has('is_active'),
                fn($q) => $q->where('is_active', $request->boolean('is_active'))
            )
            ->orderBy('price')
            ->paginate(min($request->integer('per_page', 15), 100));

        return ShippingMethodResource::collection($methods);
    }

    /**
     * Criar novo método de envio.
     */
    public function store(StoreShippingMethodRequest $request): JsonResponse
    {
        $method = ShippingMethod::create($request->validated());

        return (new ShippingMethodResource($method))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Detalhe de um método de envio.
     */
    public function show(ShippingMethod $shippingMethod): ShippingMethodResource
    {
        return new ShippingMethodResource($shippingMethod);
    }

    /**
     * Atualizar método de envio.
     */
    public function update(UpdateShippingMethodRequest $request, ShippingMethod $shippingMethod): ShippingMethodResource
    {
        $shippingMethod->update($request->validated());

        return new ShippingMethodResource($shippingMethod);
    }

    /**
     * Eliminar método de envio.
     */
    public function destroy(ShippingMethod $shippingMethod): JsonResponse
    {
        $shippingMethod->delete();

        return response()->json(null, 204);
    }
}
