<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingMethodRequest extends FormRequest
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
            'shipping_zone_id' => ['nullable', 'integer', 'exists:shipping_zones,id'],
            'name'           => ['sometimes', 'required', 'string', 'max:100'],
            'carrier'        => ['sometimes', 'required', 'string', 'max:50'],
            'price'          => ['sometimes', 'required', 'numeric', 'min:0'],
            'min_weight'     => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_weight'     => ['sometimes', 'required', 'numeric', 'gt:min_weight'],
            'estimated_days' => ['sometimes', 'required', 'string', 'max:30'],
            'is_active'      => ['boolean'],
        ];
    }
}
