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
        // Começamos por garantir que apenas os produtos ativos e com stock (opcional) aparecem no site
        $query = Product::query()
            ->with(['brand', 'category', 'primaryImage']) // lazy eager loading de relações essenciais para a listagem
            ->where('is_active', true);

        // Pesquisa de texto livre no nome do relógio
        if ($request->filled('search')) {
            $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($request->search) . '%']);
        }

        // Filtro por slug de categoria (muito útil em e-commerce nas opções de menu)
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filtro por slug de marca
        if ($request->filled('brand')) {
            $query->whereHas('brand', function ($q) use ($request) {
                $q->where('slug', $request->brand);
            });
        }

        // Filtro por Género (Masculino, Feminino, Unisexo)
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filtros avançados por preço (min_price e max_price)
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
                default:
                    $query->latest(); // Default
            }
        } else {
            $query->latest(); // Padrão
        }

        // Devolver com uma modesta paginação. 12 ou 16 relógios costuma ser boa prática visual em grid.
        $products = $query->paginate($request->input('per_page', 12));

        return ProductResource::collection($products);
    }

    /**
     * Produto em Detalhe
     */
    public function show($slug)
    {
        // Buscar o produto único por slug, garantindo sempre que está ativo
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->with(['brand', 'category', 'images']) // Trazer toda a galeria agora!
            ->firstOrFail();

        return new ProductResource($product);
    }
}
