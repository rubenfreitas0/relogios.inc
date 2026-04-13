<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class BrandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::active()
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }

    public function show(string $slug): BrandResource
    {
        $brand = Brand::active()
            ->where('slug', $slug)
            ->with(['products' => fn($q) => $q->where('is_active', true)->whereNull('deleted_at')])
            ->firstOrFail();

        return new BrandResource($brand);
    }
}

//GET http://localhost:8000/api/catalog/brands

//Headers:
  //Accept: application/json