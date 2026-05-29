<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Atualizar dados do perfil do utilizador autenticado.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'firstname' => 'sometimes|required|string|max:100',
            'lastname'  => 'sometimes|required|string|max:100',
            'phone'     => 'nullable|string|max:20',
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json([
            'message' => 'Perfil atualizado com sucesso.',
            'data'    => new UserResource($user),
        ]);
    }
}
