<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Http\Resources\ProductImageResource;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    /**
     * Listar as imagens do produto
     */
    public function index(Product $product)
    {
        return ProductImageResource::collection(
            $product->images()->orderBy('sort_order')->orderBy('id')->get()
        );
    }

    /**
     * Fazer upload de uma nova imagem para a galeria
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // Limite 2MB
        ]);

        $path = $request->file('image')->store('products', 'public');

        // A primeira imagem inserida será logo a principal (default)
        $isPrimary = $product->images()->count() === 0;

        $image = $product->images()->create([
            'url' => $path,
            'is_primary' => $isPrimary,
            'sort_order' => $product->images()->max('sort_order') + 1,
        ]);

        return response()->json([
            'message' => 'Imagem carregada com sucesso.',
            'data' => new ProductImageResource($image)
        ], 201);
    }

    /**
     * Definir a imagem como a principal (capa) do produto
     */
    public function setPrimary(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Imagem não pertence a este produto.'], 403);
        }

        $product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return response()->json([
            'message' => 'Imagem principal atualizada com sucesso.',
            'data' => new ProductImageResource($image)
        ]);
    }

    /**
     * Reordenar as imagens do produto
     */
    public function reorder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:product_images,id',
        ]);

        foreach ($request->order as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Ordem das imagens atualizada.', 'data' => ProductImageResource::collection(
            $product->images()->orderBy('sort_order')->orderBy('id')->get()
        )]);
    }

    /**
     * Apagar uma imagem da galeria
     */
    public function destroy(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            return response()->json(['message' => 'Imagem não pertence a este produto.'], 403);
        }

        $wasPrimary = $image->is_primary;

        $image->deleteImage();

        // Se a imagem apagada era a principal, elege automaticamente a próxima na lista
        if ($wasPrimary) {
            $nextImage = $product->images()->first();
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        return response()->noContent();
    }
}
