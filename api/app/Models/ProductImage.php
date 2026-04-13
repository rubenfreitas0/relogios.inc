<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'url',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /* ----- Helpers ----- */

    public function getFullUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->url);
    }

    public function deleteImage(): void
    {
        Storage::disk('public')->delete($this->url);
        $this->delete();
    }
}