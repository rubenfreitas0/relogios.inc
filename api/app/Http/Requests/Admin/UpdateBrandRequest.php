<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
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
            'name' => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('brands', 'name')->ignore($this->route('brand')),
            ],
            'logo'      => ['sometimes', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Já existe uma marca com este nome.',
            'name.max'    => 'O nome não pode ter mais de 255 caracteres.',
            'logo.image'  => 'O ficheiro tem de ser uma imagem.',
            'logo.mimes'  => 'Formatos aceites: PNG, JPG, JPEG, WEBP, SVG.',
            'logo.max'    => 'O logótipo não pode ter mais de 2 MB.',
        ];
    }
}
