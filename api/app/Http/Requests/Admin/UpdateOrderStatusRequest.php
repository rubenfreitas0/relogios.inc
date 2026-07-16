<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
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

            // Máquina de estados só-para-a-frente: proíbe reverter e saltar fases.
            if (! $from->canTransitionTo($to)) {
                $validator->errors()->add(
                    'status',
                    "Transição inválida: não é possível mudar de '{$from->label()}' para '{$to->label()}'."
                );
            }

            if ($to === OrderStatus::SHIPPED && $from !== $to
                && empty($this->input('tracking_number')) && empty($order->tracking_number)) {
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
