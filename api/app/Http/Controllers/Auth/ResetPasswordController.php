<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;
use App\Mail\ResetPasswordMail;



class ResetPasswordController extends Controller
{
    /**
     * Enviar link de recuperação de password por email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Se o e-mail existir, enviámos o link de recuperação.',
            ]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'created_at' => Carbon::now(),
            ]
        );

        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

        return response()->json([
            'message' => 'Se o e-mail existir, enviámos o link de recuperação.',
        ]);
    }

    /**
     * Redefinir a password utilizando o token recebido.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'As passwords não coincidem.',
            'token.required' => 'O token é obrigatório.'
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'message' => 'Token inválido ou e-mail incorreto.'
            ], 422);
        }

        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'O token expirou.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->update([
            'password' => $request->password
        ]);

        // Revogar tokens antigos, obrigado a fazer login com a nova password
        $user->tokens()->delete();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'A sua password foi alterada com sucesso!'
        ]);
    }
}
