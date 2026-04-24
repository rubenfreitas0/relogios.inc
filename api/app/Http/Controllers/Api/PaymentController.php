<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
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

        DB::beginTransaction();
        try {
            // Lock pessimista — impede processamento duplicado de webhooks concorrentes
            $payment = Payment::with('order')
                ->lockForUpdate()
                ->findOrFail($validated['payment_id']);

            if ($payment->status === PaymentStatus::PAID) {
                DB::commit();
                return response()->json(['message' => 'O pagamento já foi processado anteriormente.'], 200);
            }

            $payment->update([
                'status'         => PaymentStatus::PAID,
                'paid_at'        => now(),
                'transaction_id' => $validated['transaction_id'] ?? 'SIM-' . time(),
            ]);

            $payment->order->update([
                'payment_status' => PaymentStatus::PAID,
                'paid_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pagamento confirmado com sucesso.',
                'order'   => [
                    'order_number' => $payment->order->order_number,
                    'status'       => PaymentStatus::PAID->value,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao processar o pagamento.'], 500);
        }
    }
}
