<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::query()
            ->with(['brand', 'category', 'images'])
            ->when(
                $request->filled('search'),
                fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower((string) $request->search) . '%'])
            )
            ->when(
                $request->filled('brand_id'),
                fn($q) => $q->where('brand_id', $request->brand_id)
            )
            ->when(
                $request->filled('category_id'),
                fn($q) => $q->where('category_id', $request->category_id)
            )
            ->when(
                $request->has('is_active'),
                fn($q) => $q->where('is_active', $request->boolean('is_active'))
            )
            ->when(
                $request->has('gender'),
                fn($q) => $q->where('gender', $request->gender)
            )
            ->when(
                $request->has('is_featured'),
                fn($q) => $q->where('is_featured', $request->boolean('is_featured'))
            )
            ->when(
                $request->has('trashed') && $request->boolean('trashed'),
                fn($q) => $q->onlyTrashed()
            )
            ->latest()
            ->paginate($request->input('per_page', 10));

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $product = Product::create($validated);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $path,
                        'is_primary' => $index === 0, // A primeira imagem é a principal por defeito
                        'sort_order' => $index
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Produto criado com sucesso.',
                'data' => new ProductResource($product->load(['brand', 'category', 'images']))
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar o produto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load(['brand', 'category', 'images']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $product->update($validated);

            // Nota: Num sistema robusto, adicionaríamos um endpoint separado para gerir 
            // a remoção de imagens antigas. Aqui, assumimos que se o utilizador envia 'images', 
            // estamos a adicionar NOVAS imagens à galeria do produto.
            if ($request->hasFile('images')) {
                $startingSortOrder = $product->images()->max('sort_order') + 1;

                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('products', 'public');

                    ProductImage::create([
                        'product_id' => $product->id,
                        'url' => $path,
                        'is_primary' => false,
                        'sort_order' => $startingSortOrder + $index
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Produto atualizado com sucesso.',
                'data' => new ProductResource($product->load(['brand', 'category', 'images']))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar o produto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Ao fazer soft delete ao produto, as imagens mantêm-se por histórico.
        $product->delete();

        return response()->noContent();
    }
    /**
     * Atualizar apenas o stock do produto rapidamente
     */
    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock' => ['required', 'integer', 'min:0']
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Stock atualizado com sucesso.',
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Restaurar um produto apagado (Soft Delete)
     */
    public function restore(Product $product)
    {
        $product->restore();

        return response()->json([
            'message' => 'Produto restaurado com sucesso.',
            'data' => new ProductResource($product)
        ]);
    }
}
