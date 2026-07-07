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
     * Enviar código de recuperação de password por email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Se o e-mail existir, enviámos o código de recuperação.',
            ]);
        }

        // Código de 6 dígitos numérico
        $token = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token'      => Hash::make($token),
                'attempts'   => 0,
                'created_at' => Carbon::now(),
            ]
        );

        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

        return response()->json([
            'message' => 'Se o e-mail existir, enviámos o código de recuperação.',
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

        if (!$record) {
            return response()->json([
                'message' => 'Código de recuperação inválido ou e-mail incorreto.'
            ], 422);
        }

        // Expiração curta de 15 minutos
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'O código expirou. Solicite um novo código.'], 422);
        }

        if (!Hash::check($request->token, $record->token)) {
            // Incrementar tentativas
            $newAttempts = ($record->attempts ?? 0) + 1;
            
            if ($newAttempts >= 3) {
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return response()->json([
                    'message' => 'Excedeu o limite de tentativas. Solicite um novo código.'
                ], 422);
            }

            DB::table('password_reset_tokens')->where('email', $request->email)->update([
                'attempts' => $newAttempts
            ]);

            return response()->json([
                'message' => 'Código de recuperação inválido.'
            ], 422);
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
