<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        $cartItem = $this->route('cart');
        return $cartItem && $this->user()->id === $cartItem->user_id;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
