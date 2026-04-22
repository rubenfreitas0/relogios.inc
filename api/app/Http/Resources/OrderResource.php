<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_number'   => $this->order_number,
            'status'         => $this->status,
            'payment_status' => $this->payment_status,

            'customer' => [
                'firstname' => $this->shipping_firstname,
                'lastname'  => $this->shipping_lastname,
                'email'     => $this->whenLoaded('user', fn() => $this->user->email),
                'phone'     => $this->shipping_phone,
                'nif'       => $this->nif,
            ],

            'shipping_address' => [
                'address_line1' => $this->shipping_address_line1,
                'address_line2' => $this->shipping_address_line2,
                'city'          => $this->shipping_city,
                'postal_code'   => $this->shipping_postal_code,
                'country'       => $this->shipping_country,
            ],

            'shipping_method' => $this->whenLoaded(
                'shippingMethod',
                fn() => [
                    'name'           => $this->shippingMethod?->name,
                    'carrier'        => $this->shipping_carrier,
                    'estimated_days' => $this->estimated_days,
                ]
            ),

            'subtotal'      => (float) $this->subtotal,
            'shipping_cost' => (float) $this->shipping_cost,
            'total'         => (float) $this->total,

            'tracking_number' => $this->tracking_number,
            'paid_at'         => $this->paid_at?->toIso8601String(),
            'created_at'      => $this->created_at->toIso8601String(),
            'updated_at'      => $this->updated_at->toIso8601String(),

            'payments' => $this->whenLoaded('payments', function () {
                return $this->payments->map(fn($p) => [
                    'id'             => $p->id,
                    'method'         => $p->method,
                    'status'         => $p->status,
                    'amount'         => (float) $p->amount,
                    'payment_data'   => $p->payment_data,
                    'transaction_id' => $p->transaction_id,
                ]);
            }),

            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
