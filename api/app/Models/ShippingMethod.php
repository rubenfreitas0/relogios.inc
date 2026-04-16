<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    protected $fillable = [
        'name',
        'carrier',
        'price',
        'min_weight',
        'max_weight',
        'estimated_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'min_weight' => 'decimal:3',
            'max_weight' => 'decimal:3',
            'is_active'  => 'boolean',
        ];
    }

    /* ----- Relationships ----- */

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /* ----- Scopes ----- */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /* ----- Helpers ----- */

    public function supportsWeight(float $weight): bool
    {
        return $weight >= $this->min_weight && $weight <= $this->max_weight;
    }
}
