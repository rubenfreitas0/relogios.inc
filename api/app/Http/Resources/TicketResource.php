<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'subject'    => $this->subject,
            'message'    => $this->message,
            'status'     => $this->status,
            'type'       => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user'       => [
                'id'        => $this->user->id,
                'firstname' => $this->user->firstname,
                'lastname'  => $this->user->lastname,
                'email'     => $this->user->email,
                'phone'     => $this->user->phone,
                'addresses' => $this->user->addresses->map(fn($addr) => [
                    'id'          => $addr->id,
                    'street'      => $addr->street,
                    'city'        => $addr->city,
                    'postal_code' => $addr->postal_code,
                    'country'     => $addr->country,
                    'is_default'  => $addr->is_default,
                ]),
                'orders'    => $this->user->orders->map(fn($order) => [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number,
                    'total'          => $order->total,
                    'status'         => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at'     => $order->created_at,
                ]),
            ],
        ];
    }
}
