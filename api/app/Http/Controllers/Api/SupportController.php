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

        // Guardar o ticket na base de dados
        $ticket = $user->tickets()->create([
            'subject' => $subject,
            'message' => $messageContent,
            'status'  => 'open',
            'type'    => $subject, // Ex: 'devolucao', 'reembolso', etc.
        ]);

        // Enviar o email para o log/SMTP configurado como backup/notificação
        try {
            Mail::raw(
                "Mensagem de Apoio ao Cliente (Ticket #{$ticket->id}):\n\n" .
                "Cliente: {$user->firstname} {$user->lastname}\n" .
                "Email: {$user->email}\n" .
                "Assunto: {$subject}\n\n" .
                "Mensagem:\n{$messageContent}",
                function ($mail) use ($user, $subject, $ticket) {
                    $mail->to('geral@relogios.inc')
                         ->subject("[Apoio ao Cliente] Ticket #{$ticket->id} - " . ucfirst($subject) . " - {$user->firstname} {$user->lastname}");
                }
            );

            Log::info("Email de apoio ao cliente para Ticket #{$ticket->id} enviado com sucesso.");
        } catch (\Exception $e) {
            Log::error("Falha ao enviar email de apoio: " . $e->getMessage());
        }

        return response()->json([
            'message' => 'Pedido de contacto enviado com sucesso.',
            'ticket' => $ticket,
        ]);
    }
}
