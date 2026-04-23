<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                Rule::enum(OrderStatus::class),
            ],
            'tracking_number' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Validações de lógica de negócio após a validação base que impedem transições de estado inválidas.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $orderNumber = $this->route('orderNumber');
            $order       = \App\Models\Order::where('order_number', $orderNumber)->first();

            if (! $order) {
                return;
            }

            $from = $order->status;
            $to   = OrderStatus::from($this->input('status'));

            $invalidTransitions = [
                OrderStatus::DELIVERED->value => [OrderStatus::PENDING, OrderStatus::PROCESSING],
                OrderStatus::CANCELLED->value => [OrderStatus::SHIPPED, OrderStatus::DELIVERED],
            ];

            foreach ($invalidTransitions as $targetStatus => $forbiddenOrigins) {
                if ($to->value === $targetStatus && in_array($from, $forbiddenOrigins)) {
                    $validator->errors()->add(
                        'status',
                        "Não é possível mover a encomenda de '{$from->value}' para '{$to->value}'."
                    );
                }
            }

            if ($to === OrderStatus::SHIPPED && empty($this->input('tracking_number')) && empty($order->tracking_number)) {
                $validator->errors()->add(
                    'tracking_number',
                    'O número de rastreio é obrigatório ao marcar a encomenda como enviada.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'status.required' => 'O estado é obrigatório.',
            'status.in'       => 'Estado inválido. Valores aceites: pending, processing, shipped, delivered, cancelled, refunded.',
        ];
    }
}
