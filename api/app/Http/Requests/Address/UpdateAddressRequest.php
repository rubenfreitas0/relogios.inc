<?php

namespace App\Http\Requests\Address;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname'     => ['sometimes', 'required', 'string', 'max:100'],
            'lastname'      => ['sometimes', 'required', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address_line1' => ['sometimes', 'required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city'          => ['sometimes', 'required', 'string', 'max:100'],
            'postal_code'   => ['sometimes', 'required', 'string', 'max:20'],
            'country'       => ['sometimes', 'string', 'size:2'],
            'is_default'    => ['sometimes', 'boolean']
        ];
    }
}
