<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'brand_id'          => ['sometimes', 'required', 'integer', 'exists:brands,id'],
            'category_id'       => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'gender'            => ['sometimes', 'required', Rule::in(['masculino', 'feminino', 'unisexo'])],
            'name'              => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($this->route('product'))
            ],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description'       => ['nullable', 'string'],
            'price'             => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock'             => ['sometimes', 'required', 'integer', 'min:0'],
            'is_active'         => ['sometimes', 'boolean'],
            'is_featured'       => ['sometimes', 'boolean'],
            'images'            => ['sometimes', 'array', 'max:10'],
            'images.*'          => ['image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'remove_image_ids'  => ['sometimes', 'array'],
            'remove_image_ids.*' => ['integer', 'exists:product_images,id'],
            'image_order'       => ['sometimes', 'array'],
            'image_order.*'     => ['integer', 'exists:product_images,id'],
            'primary_image_id'  => ['sometimes', 'integer', 'exists:product_images,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'brand_id.exists'      => 'A marca selecionada não existe.',
            'category_id.exists'   => 'A categoria selecionada não existe.',
            'gender.in'            => 'O género deve ser: masculino, feminino ou unisexo.',
            'name.unique'          => 'Já existe um produto com este nome.',
            'name.max'             => 'O nome não pode ter mais de 255 caracteres.',
            'price.numeric'        => 'O preço deve ser um número.',
            'price.min'            => 'O preço não pode ser negativo.',
            'stock.integer'        => 'O stock deve ser um número inteiro.',
            'stock.min'            => 'O stock não pode ser negativo.',
            'images.array'         => 'As imagens devem ser enviadas em array.',
            'images.max'           => 'Só pode enviar no máximo 10 imagens.',
            'images.*.image'       => 'Cada ficheiro tem de ser uma imagem.',
            'images.*.mimes'       => 'Formatos aceites: PNG, JPG, JPEG.',
            'images.*.max'         => 'Cada imagem não pode ter mais de 2 MB.',
            'remove_image_ids.array' => 'O campo de remoção deve ser um array.',
            'remove_image_ids.*.exists' => 'Uma das imagens selecionadas para remover não existe.',
            'image_order.array'    => 'O campo de ordenação deve ser um array.',
            'image_order.*.exists' => 'Uma das imagens na ordem selecionada não existe.',
            'primary_image_id.exists' => 'A imagem selecionada como principal não existe.',
        ];
    }
}
