<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FilterOption;
use Illuminate\Http\Request;

class FilterController extends Controller
{
    /**
     * Opções de filtro públicas, agrupadas, para as páginas de categoria.
     * GET /api/catalog/filters?gender=homens
     */
    public function index(Request $request)
    {
        $query = FilterOption::query()->orderBy('sort_order');

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $options = $query->get()->groupBy('gender')->map(
            fn($byGender) => $byGender->groupBy('group')->map(
                fn($byGroup) => $byGroup->map(fn($o) => [
                    'id'    => $o->id,
                    'label' => $o->label,
                    'value' => $o->value,
                    'meta'  => $o->meta,
                ])->values()
            )
        );

        return response()->json(['data' => $options]);
    }
}
