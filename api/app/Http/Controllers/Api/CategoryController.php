<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Listar categorias ativas.
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::active()
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Detalhe de uma categoria
     */
    public function show(string $slug): CategoryResource
    {
        $category = Category::active()
            ->where('slug', $slug)
            ->withCount('products')
            ->firstOrFail();

        return new CategoryResource($category);
    }
}
