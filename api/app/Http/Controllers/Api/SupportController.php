<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SupportController extends Controller
{
    /**
     * Envia uma mensagem de apoio ao cliente.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:100',
            'message' => 'required|string|max:5000',
        ]);

        $user = $request->user();
        $subject = $validated['subject'];
        $messageContent = $validated['message'];

        // Enviar o email para o log/SMTP configurado
        try {
            Mail::raw(
                "Mensagem de Apoio ao Cliente:\n\n" .
                "Cliente: {$user->firstname} {$user->lastname}\n" .
                "Email: {$user->email}\n" .
                "Assunto: {$subject}\n\n" .
                "Mensagem:\n{$messageContent}",
                function ($mail) use ($user, $subject) {
                    $mail->to('geral@relogios.inc')
                         ->subject("[Apoio ao Cliente] " . ucfirst($subject) . " - {$user->firstname} {$user->lastname}");
                }
            );

            Log::info("Email de apoio ao cliente enviado com sucesso para geral@relogios.inc. Assunto: {$subject}");
        } catch (\Exception $e) {
            Log::error("Falha ao enviar email de apoio: " . $e->getMessage());
            // Mesmo se o mailer falhar localmente por falta de rede, registamos no log e informamos
        }

        return response()->json([
            'message' => 'Pedido de contacto enviado com sucesso.',
        ]);
    }
}
