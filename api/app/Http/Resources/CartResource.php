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
                // Garantir que devolve o path da imagem e caso não haja, tenta não rebentar
                'image' => $this->product->primaryImage ? $this->product->primaryImage->image_path : null 
            ],
            // Calcula logo na API o subtotal da linha
            'line_total' => round($this->quantity * $this->product->price, 2),
        ];
    }
}
