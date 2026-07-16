<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class BrandController extends Controller
{
    /**
     * Listar todas as marcas ativas.
     */
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::active()
            ->orderBy('name')
            ->paginate(min(request()->integer('per_page', 15), 100));

        return BrandResource::collection($brands);
    }

    /**
     * Detalhe de uma marca (por slug) e seus produtos.
     */
    public function show(string $slug): BrandResource
    {
        $brand = Brand::active()
            ->where('slug', $slug)
            ->with(['products' => fn($q) => $q->where('is_active', true)->whereNull('deleted_at')->with(['brand', 'categories', 'primaryImage'])->latest()->take(20)])
            ->firstOrFail();

        return new BrandResource($brand);
    }
}
