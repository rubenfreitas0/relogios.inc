<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            'price'             => $this->price,
            'stock'             => $this->stock,
            'is_active'         => $this->is_active,
            'is_featured'       => $this->is_featured,
            'gender'            => $this->gender,

            // Relações (só aparecem se carregadas)
            'brand'    => new BrandResource($this->whenLoaded('brand')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'images'   => ProductImageResource::collection($this->whenLoaded('images')),

            // Contagens (só aparecem se pedidas)
            'images_count' => $this->whenCounted('images'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}