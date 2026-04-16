<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'brand_id'          => ['required', 'integer', 'exists:brands,id'],
            'category_id'       => ['required', 'integer', 'exists:categories,id'],
            'gender'            => ['required', Rule::in(['masculino', 'feminino', 'unisexo'])],
            'name'              => ['required', 'string', 'max:255', 'unique:products,name'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'stock'             => ['required', 'integer', 'min:0'],
            'is_active'         => ['sometimes', 'boolean'],
            'is_featured'       => ['sometimes', 'boolean'],
            'images'            => ['sometimes', 'array', 'max:10'],
            'images.*'          => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'primary_image'     => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.required'    => 'A marca é obrigatória.',
            'brand_id.exists'      => 'A marca selecionada não existe.',
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.exists'   => 'A categoria selecionada não existe.',
            'gender.required'      => 'O género é obrigatório.',
            'gender.in'            => 'O género deve ser: masculino, feminino ou unisexo.',
            'name.required'        => 'O nome do produto é obrigatório.',
            'name.unique'          => 'Já existe um produto com este nome.',
            'name.max'             => 'O nome não pode ter mais de 255 caracteres.',
            'price.required'       => 'O preço é obrigatório.',
            'price.numeric'        => 'O preço deve ser um número.',
            'price.min'            => 'O preço não pode ser negativo.',
            'stock.required'       => 'O stock é obrigatório.',
            'stock.integer'        => 'O stock deve ser um número inteiro.',
            'stock.min'            => 'O stock não pode ser negativo.',
            'images.array'         => 'As imagens devem ser enviadas em array.',
            'images.max'           => 'Só pode enviar no máximo 10 imagens.',
            'images.*.image'       => 'Cada ficheiro tem de ser uma imagem.',
            'images.*.mimes'       => 'Formatos aceites: PNG, JPG, JPEG, WebP.',
            'images.*.max'         => 'Cada imagem não pode ter mais de 2 MB.',
        ];
    }
}
