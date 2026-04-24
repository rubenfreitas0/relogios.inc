<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use App\Http\Requests\Cart\StoreCartRequest;
use App\Http\Requests\Cart\UpdateCartRequest;
use App\Http\Resources\CartResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Listar o conteúdo do carrinho do user.
     */
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()->with(['product.primaryImage'])->get();

        $total = $cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        return response()->json([
            'items' => CartResource::collection($cartItems),
            'cart_total' => round($total, 2)
        ]);
    }

    /**
     * Adicionar produto ao carrinho.
     */
    public function store(StoreCartRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $product = Product::findOrFail($validated['product_id']);

        // Impedimento: Produto não está ativo / disponivel
        if (!$product->is_active) {
            return response()->json(['message' => 'Este produto de momento não está à venda.'], 403);
        }

        $requestedQuantity = $validated['quantity'];

        $cartItem = DB::transaction(function () use ($user, $product, $requestedQuantity) {
            // Lock para evitar duplicação por cliques rápidos
            $cartItem = $user->cartItems()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newTotalQuantity = $cartItem ? ($cartItem->quantity + $requestedQuantity) : $requestedQuantity;

            // Impedimento: O utilizador tenta adicionar ao carrinho mais unidades do que a loja tem
            if ($newTotalQuantity > $product->stock) {
                abort(422, 'Stock insuficiente. Apenas existem ' . $product->stock . ' unidades disponíveis.');
            }

            if ($cartItem) {
                $cartItem->update(['quantity' => $newTotalQuantity]);
            } else {
                $cartItem = $user->cartItems()->create([
                    'product_id' => $product->id,
                    'quantity' => $requestedQuantity
                ]);
            }

            return $cartItem;
        });

        $cartItem->load('product.primaryImage');

        return response()->json([
            'message' => 'Produto adicionado ao carrinho com sucesso.',
            'data' => new CartResource($cartItem)
        ]);
    }

    /**
     * Atualiza a quantidade de um item no carrinho
     */
    public function update(UpdateCartRequest $request, CartItem $cart)
    {
        $cart->load('product');
        $cart->product->refresh(); // Garantir stock atualizado da BD

        if ($request->user()->id !== $cart->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validated();

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
     * Remover um item do carrinho.
     */
    public function destroy(Request $request, CartItem $cart)
    {
        if ($request->user()->id !== $cart->user_id) {
            return response()->json(['message' => 'Sem permissões.'], 403);
        }

        $cart->delete();

        return response()->noContent();
    }

    /**
     * Esvaziar carrinho inteiro
     */
    public function clear(Request $request)
    {
        $request->user()->cartItems()->delete();

        return response()->noContent();
    }
}
