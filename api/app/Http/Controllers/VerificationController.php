<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Verificar email — rota PÚBLICA com link assinado
     * Não exige autenticação
     */
    public function verify(Request $request, $id): JsonResponse
    {
        // O middleware 'signed' já validou a assinatura e expiração
        // Se chegou aqui, o link é válido

        $user = User::findOrFail($id);

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message'          => 'O teu email já estava verificado.',
                'already_verified' => true,
            ], 200);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'message'  => 'Email verificado com sucesso! Já podes fazer compras.',
            'verified' => true,
        ], 200);
    }

    /**
     * Reenviar email de verificação — exige autenticação
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'O teu email já está verificado.',
            ], 200);
        }

        $user->notify(new VerifyEmailNotification());

        return response()->json([
            'message' => 'Email de verificação reenviado.',
        ], 200);
    }

    /**
     * Verificar estado — para o frontend saber se precisa mostrar aviso
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'email_verified' => $user->hasVerifiedEmail(),
            'email'          => $user->email,
        ]);
    }
}
