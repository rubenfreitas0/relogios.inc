<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        // A autorização baseia-se em estar logado na API
        return true; 
    }

    public function rules(): array
    {
        return [
            'firstname'     => ['required', 'string', 'max:100'],
            'lastname'      => ['required', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city'          => ['required', 'string', 'max:100'],
            'postal_code'   => ['required', 'string', 'max:20'],
            'country'       => ['sometimes', 'string', 'size:2'], // Formato ISO, ex: PT
            'is_default'    => ['sometimes', 'boolean']
        ];
    }
}
