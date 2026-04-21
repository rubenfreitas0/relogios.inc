<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
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

    /* ----- Relationships ----- */

    public function countries()
    {
        return $this->hasMany(ShippingZoneCountry::class);
    }

    public function shippingMethods()
    {
        return $this->hasMany(ShippingMethod::class);
    }
}
