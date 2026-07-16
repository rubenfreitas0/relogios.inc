<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\FilterOption;
use Illuminate\Http\Request;

/**
 * Gestão das opções de filtro das páginas de categoria.
 *
 * IMPORTANTE: só é possível VER e EDITAR. Não existem rotas de
 * criação nem eliminação — os grupos e o número de opções são fixos.
 */
class FilterOptionController extends Controller
{
    public function index(Request $request)
    {
        $query = FilterOption::query()
            ->orderBy('gender')
            ->orderBy('group')
            ->orderBy('sort_order');

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function show(FilterOption $filter)
    {
        return response()->json(['data' => $filter]);
    }

    public function update(Request $request, FilterOption $filter)
    {
        // gender e group são imutáveis — só o conteúdo da opção é editável
        $validated = $request->validate([
            'label'      => 'sometimes|required|string|max:100',
            'value'      => 'nullable|string|max:100',
            'meta'       => 'nullable|array',
            'meta.min'   => 'nullable|numeric|min:0',
            'meta.max'   => 'nullable|numeric|min:0',
            'sort_order' => 'sometimes|integer|min:0',
        ]);

        $filter->update($validated);

        return response()->json([
            'message' => 'Opção de filtro atualizada.',
            'data'    => $filter->fresh(),
        ]);
    }
}
