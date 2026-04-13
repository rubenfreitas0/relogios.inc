<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Http\Requests\Cart\StoreCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Display a listing of the user's cart.
     */
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()->with(['product.primaryImage'])->get();
        
        // Formatar o array para devolver também o total geral calculado
        $total = $cartItems->sum(function($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'items' => CartResource::collection($cartItems),
            'cart_total' => round($total, 2)
        ]);
    }

    /**
     * Add product to cart.
     */
    public function store(StoreCartRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        
        $product = Product::findOrFail($validated['product_id']);

        // Bloqueador nº 1: Produto não está ativo
        if (!$product->is_active) {
            return response()->json(['message' => 'Este produto de momento não está à venda.'], 403);
        }

        // Tentar ver se o utilizador já tem este produto no carrinho dele
        $cartItem = $user->cartItems()->where('product_id', $product->id)->first();
        
        $requestedQuantity = $validated['quantity'];
        $newTotalQuantity = $cartItem ? ($cartItem->quantity + $requestedQuantity) : $requestedQuantity;

        // Bloqueador nº 2: O utilizador tenta meter no carrinho mais do que a loja tem (Opção que falámos)
        if ($newTotalQuantity > $product->stock) {
            return response()->json([
                'message' => 'Stock insuficiente. Apenas existem ' . $product->stock . ' unidades disponíveis.'
            ], 422);
        }

        if ($cartItem) {
            // Se já existia, soma
            $cartItem->update(['quantity' => $newTotalQuantity]);
        } else {
            // Se não existia, insere linha nova
            $cartItem = $user->cartItems()->create([
                'product_id' => $product->id,
                'quantity' => $requestedQuantity
            ]);
        }

        // Load da relation para devolver o resource completo
        $cartItem->load('product.primaryImage');

        return response()->json([
            'message' => 'Produto adicionado ao carrinho com sucesso.',
            'data' => new CartResource($cartItem)
        ]);
    }

    /**
     * Update cart item quantity (e.g. + / - buttons)
     */
    public function update(UpdateCartRequest $request, CartItem $cart)
    {
        $validated = $request->validated();
        
        // Verifica no momento do update se continua a não ultrapassar o stock
        if ($validated['quantity'] > $cart->product->stock) {
            return response()->json([
                'message' => 'Stock insuficiente para a quantidade desejada.'
            ], 422);
        }

        $cart->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Quantidade atualizada no carrinho.',
            'data' => new CartResource($cart->load('product.primaryImage'))
        ]);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, CartItem $cart)
    {
        if ($request->user()->id !== $cart->user_id) {
            return response()->json(['message' => 'Sem permissões.'], 403);
        }

        $cart->delete();

        return response()->json(['message' => 'Item removido do carrinho.']);
    }

    /**
     * Esvaziar carrinho inteiro
     */
    public function clear(Request $request)
    {
        $request->user()->cartItems()->delete();

        return response()->json(['message' => 'Carrinho esvaziado com sucesso.']);
    }
}
