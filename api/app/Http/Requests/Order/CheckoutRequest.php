<?php

namespace App\Http\Requests\Order;

use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Método de envio — obrigatório e ativo
            'shipping_method_id' => ['required', 'integer', 'exists:shipping_methods,id'],

            // Pagamento
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'payment_phone'  => [
                Rule::requiredIf(fn() => $this->payment_method === PaymentMethod::MBWAY->value),
                'nullable', 'string', 'max:20'
            ],

            // NIF — opcional
            'nif' => ['nullable', 'string', 'max:20'],

            // Endereço guardado (opcional — se fornecido, usa os dados do perfil)
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],

            // Campos manuais (obrigatórios se address_id não for fornecido)
            'firstname'     => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'lastname'      => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'address_line1' => ['required_without:address_id', 'nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city'          => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'postal_code'   => ['required_without:address_id', 'nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'size:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_method_id.required' => 'O método de envio é obrigatório.',
            'shipping_method_id.exists'   => 'O método de envio selecionado não existe.',
            'payment_method.required'     => 'O método de pagamento é obrigatório.',
            'payment_method.Illuminate\Validation\Rules\Enum' => 'Método de pagamento inválido.',
            'payment_phone.required'      => 'O número de telemóvel é obrigatório para pagamentos via MBWay.',
            'firstname.required_without'  => 'O nome é obrigatório quando não é fornecido um endereço guardado.',
            'lastname.required_without'   => 'O apelido é obrigatório quando não é fornecido um endereço guardado.',
            'address_line1.required_without' => 'A morada é obrigatória quando não é fornecido um endereço guardado.',
            'city.required_without'       => 'A cidade é obrigatória quando não é fornecido um endereço guardado.',
            'postal_code.required_without'=> 'O código postal é obrigatório quando não é fornecido um endereço guardado.',
            'country.size'                => 'O país deve ser um código ISO de 2 letras (ex: PT).',
        ];
    }
}
