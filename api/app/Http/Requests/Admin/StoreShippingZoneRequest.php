<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100', 'unique:shipping_zones,name'],
            'is_active'   => ['boolean'],
            'countries'   => ['sometimes', 'array'],
            'countries.*' => ['string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da zona é obrigatório.',
            'name.unique'   => 'Já existe uma zona com este nome.',
            'countries.*.size' => 'Cada código de país deve ter exatamente 2 letras (ex: PT).',
        ];
    }
}
