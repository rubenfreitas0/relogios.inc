<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Listar catálogo público de produtos com filtros
     */
    public function index(Request $request)
    {
        // Filtro de produtos ativos e com stock
        $query = Product::query()
            ->with(['brand', 'category', 'primaryImage'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower((string) $request->search) . '%']);
        }

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Ordenação inteligente
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->latest();
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        // Paginação (12)
        $products = $query->paginate($request->input('per_page', 12));

        return ProductResource::collection($products);
    }

    /**
     * Pesquisar produtos em destaque (Featured)
     */
    public function featured()
    {
        $products = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['brand', 'category', 'primaryImage'])
            ->latest()
            ->take(8)
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Produto em Detalhe
     */
    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['brand', 'category', 'images'])
            ->firstOrFail();

        return new ProductResource($product);
    }
}
