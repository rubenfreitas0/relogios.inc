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
            ->with(['brand', 'categories', 'primaryImage'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower((string) $request->search) . '%']);
        }

        // Aceita uma ou várias categorias (CSV ou array). Semântica AND: o
        // produto tem de pertencer a TODAS as categorias indicadas — permite
        // cruzar, por exemplo, um Tipo (clássicos) com um Mecanismo (analogico).
        if ($request->filled('category')) {
            $slugs = is_array($request->category)
                ? $request->category
                : explode(',', (string) $request->category);

            foreach (array_filter(array_map('trim', $slugs)) as $slug) {
                $query->whereHas('categories', function ($q) use ($slug) {
                    $q->where('slug', $slug);
                });
            }
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        if ($request->filled('gender')) {
            $gender = $request->gender;
            if ($gender === 'homens') {
                $gender = 'masculino';
            } elseif ($gender === 'mulheres') {
                $gender = 'feminino';
            }
            $query->where('gender', $gender);
        }

        if ($request->filled('color')) {
            $query->where('color', strtolower((string) $request->color));
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
        $products = $query->paginate(min($request->integer('per_page', 12), 100));

        return ProductResource::collection($products);
    }

    /**
     * Pesquisar produtos em destaque (Featured)
     */
    public function featured()
    {
        $products = Product::where('is_active', true)
            ->where('is_featured', true)
            ->with(['brand', 'categories', 'primaryImage'])
            ->latest()
            ->take(8)
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Produto em Detalhe
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['brand', 'categories', 'images', 'primaryImage'])
            ->firstOrFail();

        return new ProductResource($product);
    }

    /**
     * Obter Produtos Relacionados (mesma categoria)
     */
    public function related(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categoryIds = $product->categories()->pluck('categories.id');

        $related = Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->with(['brand', 'categories', 'primaryImage'])
            ->inRandomOrder()
            ->take(4)
            ->get();

        return ProductResource::collection($related);
    }
}
