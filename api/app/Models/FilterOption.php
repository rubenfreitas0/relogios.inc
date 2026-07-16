<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FilterOption extends Model
{
    /**
     * Nota: as opções de filtro são fixas (semeadas via seeder).
     * A API só permite ver e editar — nunca criar ou eliminar.
     */
    protected $fillable = [
        'gender',
        'group',
        'label',
        'value',
        'meta',
        'sort_order',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
