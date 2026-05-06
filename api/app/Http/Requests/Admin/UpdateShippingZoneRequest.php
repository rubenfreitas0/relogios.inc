<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => [
                'sometimes', 'required', 'string', 'max:100',
                Rule::unique('shipping_zones', 'name')->ignore($this->route('shipping_zone')),
            ],
            'is_active'   => ['sometimes', 'boolean'],
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
