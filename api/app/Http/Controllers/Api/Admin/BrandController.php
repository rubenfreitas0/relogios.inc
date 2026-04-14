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
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Filtros, ordenação e paginação
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
            ->paginate($request->integer('per_page', 15));

        return BrandResource::collection($brands);
    }

    /**
     * Criar nova marca.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['logo'] = $request->file('logo')->store('brands', 'public');

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

        if ($request->hasFile('logo')) {
            Storage::disk('public')->delete($brand->logo);
            $validated['logo'] = $request->file('logo')->store('brands', 'public');
        }

        $brand->update($validated);

        return new BrandResource($brand);
    }

    /**
     * Eliminar marca.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        if ($brand->products()->exists()) {
            return response()->json([
                'message' => 'Não é possível eliminar esta marca porque tem produtos associados.',
            ], 409);
        }

        Storage::disk('public')->delete($brand->logo);

        $brand->delete();

        return response()->json(null, 204);
    }
}
