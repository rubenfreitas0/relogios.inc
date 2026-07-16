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
            'discount_price'    => $this->discount_price,
            'stock'             => $this->stock,
            'color'             => $this->color,
            'weight'            => $this->weight,
            'is_active'         => $this->is_active,
            'is_featured'       => $this->is_featured,
            'gender'            => $this->gender,
            'features'          => $this->features,
            'in_the_box'        => $this->in_the_box,

            'brand'      => new BrandResource($this->whenLoaded('brand')),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            // Compatibilidade: primeira categoria (ou null) quando a relação está carregada
            'category'   => $this->whenLoaded('categories', fn () => $this->categories->first()
                ? new CategoryResource($this->categories->first())
                : null),
            'images'   => ProductImageResource::collection($this->whenLoaded('images')),
            'primary_image' => new ProductImageResource($this->whenLoaded('primaryImage')),

            'images_count' => $this->whenCounted('images'),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}
