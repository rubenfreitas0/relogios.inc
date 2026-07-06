<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Listar todos os utilizadores (clientes e admins).
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Filtro por termo de pesquisa (nome ou email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'LIKE', "%{$search}%")
                  ->orWhere('lastname', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('isActive')) {
            $query->where('is_active', $request->boolean('isActive'));
        }

        // Ordenação
        $sortBy = $request->input('sortBy', 'id');
        $direction = $request->input('sortingOrder', 'desc');

        if ($sortBy === 'fullname') {
            $query->orderBy('firstname', $direction)->orderBy('lastname', $direction);
        } else {
            $query->orderBy($sortBy, $direction);
        }

        $users = $query->paginate(100);

        // Mapear para o formato que o Vuestic espera
        $mappedData = collect($users->items())->map(function (User $user) {
            return [
                'id'       => $user->id,
                'fullname' => $user->firstname . ' ' . $user->lastname,
                'email'    => $user->email,
                'role'     => $user->role === 'admin' ? 'admin' : 'user',
                'active'   => (bool) $user->is_active,
                'username' => strstr($user->email, '@', true),
                'avatar'   => '',
                'projects' => [],
                'notes'    => '',
            ];
        });

        return response()->json([
            'data' => $mappedData,
            'pagination' => [
                'page'    => $users->currentPage(),
                'perPage' => $users->perPage(),
                'total'   => $users->total(),
            ],
        ]);
    }

    /**
     * Criar novo utilizador.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:200',
            'email'    => 'required|email|max:255|unique:users,email',
            'role'     => 'required|string|in:admin,user,owner',
            'active'   => 'required|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        $parts = explode(' ', $validated['fullname'], 2);
        $firstname = $parts[0];
        $lastname = $parts[1] ?? '';

        $user = User::create([
            'firstname'         => $firstname,
            'lastname'          => $lastname,
            'email'             => $validated['email'],
            'password'          => bcrypt($request->input('password', 'password')),
            'role'              => $validated['role'] === 'admin' ? 'admin' : 'customer',
            'is_active'         => $validated['active'],
            'email_verified_at' => now(), // Auto-verificar email
        ]);

        return response()->json([
            'id'       => $user->id,
            'fullname' => $user->firstname . ' ' . $user->lastname,
            'email'    => $user->email,
            'role'     => $user->role === 'admin' ? 'admin' : 'user',
            'active'   => (bool) $user->is_active,
            'username' => strstr($user->email, '@', true),
            'avatar'   => '',
            'projects' => [],
            'notes'    => '',
        ], 201);
    }

    /**
     * Atualizar utilizador.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:200',
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => 'required|string|in:admin,user,owner',
            'active'   => 'required|boolean',
        ]);

        $parts = explode(' ', $validated['fullname'], 2);
        $firstname = $parts[0];
        $lastname = $parts[1] ?? '';

        $user->update([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'email'     => $validated['email'],
            'role'      => $validated['role'] === 'admin' ? 'admin' : 'customer',
            'is_active' => $validated['active'],
        ]);

        return response()->json([
            'id'       => $user->id,
            'fullname' => $user->firstname . ' ' . $user->lastname,
            'email'    => $user->email,
            'role'     => $user->role === 'admin' ? 'admin' : 'user',
            'active'   => (bool) $user->is_active,
            'username' => strstr($user->email, '@', true),
            'avatar'   => '',
            'projects' => [],
            'notes'    => '',
        ]);
    }

    /**
     * Eliminar utilizador.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        // Não permitir que o utilizador autenticado se elimine a si próprio
        if ($request->user()?->id === $user->id) {
            return response()->json(['message' => 'Não te podes eliminar a ti próprio.'], 400);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}
