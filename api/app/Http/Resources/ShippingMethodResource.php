<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'carrier'        => $this->carrier,
            'price'          => (float) $this->price,
            'min_weight'     => (float) $this->min_weight,
            'max_weight'     => (float) $this->max_weight,
            'estimated_days' => $this->estimated_days,
            'is_active'      => (bool) $this->is_active,
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
