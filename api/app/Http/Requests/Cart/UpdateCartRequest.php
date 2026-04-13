<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Uma pessoa só pode alterar as quantidades do seu próprio item no carrinho
        $cartItem = $this->route('cart'); // O parametro de rota costuma ser chamado '{cart}' nestas resource routes
        return $cartItem && $this->user()->id === $cartItem->user_id;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
