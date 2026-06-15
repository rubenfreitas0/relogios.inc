<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function countries()
    {
        return $this->hasMany(ShippingZoneCountry::class);
    }

    public function shippingMethods()
    {
        return $this->hasMany(ShippingMethod::class);
    }
}
