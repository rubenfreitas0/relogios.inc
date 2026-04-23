<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_image',
        'unit_price',
        'quantity',
        'item_total',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'item_total' => 'decimal:2',
            'quantity'   => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Garante que o item_total é sempre o produto da quantidade pelo preço unitário
        static::saving(function (OrderItem $item) {
            $item->item_total = $item->unit_price * $item->quantity;
        });
    }

    /* ----- Relationships ----- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /* ----- Helpers ----- */

    /**
     * Retorna a URL da imagem do produto no momento da compra.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->product_image) {
            return null;
        }

        if (filter_var($this->product_image, FILTER_VALIDATE_URL)) {
            return $this->product_image;
        }

        return asset('storage/' . $this->product_image);
    }
}