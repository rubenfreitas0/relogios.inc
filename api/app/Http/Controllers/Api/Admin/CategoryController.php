<?php

namespace App\Http\Controllers\Api\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /**
     * Filtros, ordenação e paginação.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = Category::query()
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

        return CategoryResource::collection($categories);
    }

    /**
     * Criar nova categoria (subcategoria de um grupo tipo/mecanismo).
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = Category::create($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Detalhe de uma categoria por ID.
     */
    public function show(Category $category): CategoryResource
    {
        $category->loadCount('products');

        return new CategoryResource($category);
    }

    /**
     * Atualizar categoria.
     */
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    /**
     * Desativar categoria (soft: is_active = false, não apaga).
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->update(['is_active' => false]);

        return response()->json([
            'message' => 'Categoria desativada com sucesso.'
        ], 200);
    }
}
