<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreShippingMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:100'],
            'carrier'        => ['required', 'string', 'max:50'],
            'price'          => ['required', 'numeric', 'min:0'],
            'min_weight'     => ['required', 'numeric', 'min:0'],
            'max_weight'     => ['required', 'numeric', 'gt:min_weight'],
            'estimated_days' => ['required', 'string', 'max:30'],
            'is_active'      => ['boolean'],
        ];
    }
}
