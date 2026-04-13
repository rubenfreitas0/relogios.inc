<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Notifications\VerifyEmailNotification;
use App\Http\Resources\UserResource;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname'  => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'phone'     => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'firstname' => $validated['firstname'],
            'lastname'  => $validated['lastname'],
            'email'     => $validated['email'],
            'password'  => $validated['password'],
            'phone'     => $validated['phone'] ?? null,
        ]);

        $user->notify(new VerifyEmailNotification());

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => new UserResource($user),
            'token' => $token,
            'message' => 'Conta criada! Verifica o teu email para poderes comprar.',
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Conta desativada',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user'  => new \App\Http\Resources\UserResource($user),
            'token' => $token,
        ]);

    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sessão terminada com sucesso.',
        ]);
    }
}
