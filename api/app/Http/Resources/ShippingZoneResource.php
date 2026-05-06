<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingZoneResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'is_active' => (bool) $this->is_active,
            'countries' => $this->whenLoaded('countries', fn() =>
                $this->countries->pluck('country_code')
            ),
            'shipping_methods_count' => $this->whenCounted('shippingMethods'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
