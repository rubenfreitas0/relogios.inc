<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    /**
     * Filtros, ordenação e paginação.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $brands = Brand::query()
            ->when(
                $request->filled('search'),
                fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower((string) $request->search) . '%'])
            )
            ->when(
                $request->has('is_active'),
                fn($q) => $q->where('is_active', $request->boolean('is_active'))
            )
            ->withCount('products')
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 15), 100));

        return BrandResource::collection($brands);
    }

    /**
     * Criar nova marca.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $brand = Brand::create($validated);

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Detalhe de uma marca por ID.
     */
    public function show(Brand $brand): BrandResource
    {
        $brand->loadCount('products');

        return new BrandResource($brand);
    }

    /**
     * Atualizar marca.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $validated = $request->validated();

        $brand->update($validated);

        return new BrandResource($brand);
    }

    /**
     * Eliminar (Desativar) marca.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $brand->update(['is_active' => false]);

        return response()->json([
            'message' => 'Marca desativada com sucesso.'
        ], 200);
    }
}
