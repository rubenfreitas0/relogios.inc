<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:100', 'unique:categories,name'],
            'group'     => ['required', Rule::in(['tipo', 'mecanismo'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.unique'   => 'Já existe uma categoria com este nome.',
            'name.max'      => 'O nome não pode ter mais de 100 caracteres.',
            'group.required' => 'O grupo é obrigatório.',
            'group.in'       => 'O grupo deve ser: tipo ou mecanismo.',
        ];
    }
}
