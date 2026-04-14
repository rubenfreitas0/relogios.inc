<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            // Agrupar visualmente o produto com a sua primaryImage
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'price' => $this->product->price,
                'stock' => $this->product->stock,
                // Garantir que devolve a URL completa da imagem
                'image' => $this->product->primaryImage ? $this->product->primaryImage->full_url : null 
            ],
            // Calcula logo na API o subtotal da linha
            'line_total' => round($this->quantity * $this->product->price, 2),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
