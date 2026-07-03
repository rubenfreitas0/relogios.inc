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
            return $item->quantity * ($item->product->discount_price ?? $item->product->price);
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

        $requestedQuantity = $validated['quantity'];

        $cartItem = DB::transaction(function () use ($user, $validated, $requestedQuantity) {
            // Lock no produto para evitar overselling (Race Condition resolvido)
            $product = Product::lockForUpdate()->findOrFail($validated['product_id']);

            // Impedimento: Produto não está ativo / disponivel
            if (!$product->is_active) {
                abort(403, 'Este produto de momento não está à venda.');
            }

            // Lock para evitar duplicação por cliques rápidos
            $cartItem = $user->cartItems()
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $newTotalQuantity = $cartItem ? ($cartItem->quantity + $requestedQuantity) : $requestedQuantity;

            // Impedimento: O utilizador não pode adicionar ao carrinho mais unidades do que a loja tem
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

            $cartItem->setRelation('product', $product);

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
        if ($request->user()->id !== $cart->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validated();

        $cart = DB::transaction(function () use ($cart, $validated) {
            // Lock no carrinho e no produto para evitar Race Conditions
            $cart = CartItem::lockForUpdate()->findOrFail($cart->id);
            $product = Product::lockForUpdate()->findOrFail($cart->product_id);

            if ($validated['quantity'] > $product->stock) {
                abort(422, 'Stock insuficiente para a quantidade desejada.');
            }

            $cart->update(['quantity' => $validated['quantity']]);
            $cart->setRelation('product', $product);

            return $cart;
        });

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
