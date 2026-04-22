<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Webhook simulado para receção de pagamentos.
     */
    public function webhook(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_id'     => ['required', 'integer', 'exists:payments,id'],
            'transaction_id' => ['nullable', 'string'],
        ]);

        $payment = Payment::with('order')->findOrFail($validated['payment_id']);

        if ($payment->status === 'paid') {
            return response()->json(['message' => 'O pagamento já foi processado anteriormente.'], 200);
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status'         => 'paid',
                'paid_at'        => now(),
                'transaction_id' => $validated['transaction_id'] ?? 'SIM-' . time(),
            ]);

            $payment->order->update([
                'payment_status' => 'paid',
                'paid_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pagamento confirmado com sucesso.',
                'order'   => [
                    'order_number' => $payment->order->order_number,
                    'status'       => 'paid',
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao processar o pagamento.'], 500);
        }
    }
}
