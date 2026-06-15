<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    /**
     * Helper: cria uma Order + Payment pendente para simular webhook.
     */
    private function createOrderWithPayment(): array
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $shipping = $this->setupShipping();
        $product = $this->createProduct(['price' => 100.00, 'stock' => 10, 'weight' => 0.5]);
        $this->addToCart($user, $product, 1);

        $this->performCheckout($headers, $shipping['shippingMethod']->id);

        $order = Order::first();
        $payment = Payment::first();

        return compact('order', 'payment');
    }

    // WEBHOOK - happy path

    public function test_webhook_confirms_payment(): void
    {
        ['order' => $order, 'payment' => $payment] = $this->createOrderWithPayment();

        $response = $this->postJson('/api/payments/webhook', [
            'payment_id'     => $payment->id,
            'transaction_id' => 'TXN-12345',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Pagamento confirmado com sucesso.');

        // Payment deve estar PAID
        $this->assertEquals(PaymentStatus::PAID, $payment->fresh()->status);

        // Order payment_status deve estar PAID
        $this->assertEquals(PaymentStatus::PAID, $order->fresh()->payment_status);
    }

    public function test_webhook_transitions_order_to_processing(): void
    {
        ['order' => $order, 'payment' => $payment] = $this->createOrderWithPayment();

        // Confirmar que começa em PENDING
        $this->assertEquals(OrderStatus::PENDING, $order->status);

        $this->postJson('/api/payments/webhook', [
            'payment_id'     => $payment->id,
            'transaction_id' => 'TXN-99999',
        ]);

        // Após pagamento, deve transitar para PROCESSING
        $this->assertEquals(OrderStatus::PROCESSING, $order->fresh()->status);
    }

    // WEBHOOK - idempotency

    public function test_webhook_is_idempotent(): void
    {
        ['payment' => $payment] = $this->createOrderWithPayment();

        // Primeira chamada
        $this->postJson('/api/payments/webhook', [
            'payment_id' => $payment->id,
        ])->assertStatus(200);

        // Segunda chamada — deve devolver 200 "já processado"
        $response = $this->postJson('/api/payments/webhook', [
            'payment_id' => $payment->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'O pagamento já foi processado anteriormente.');
    }

    // WEBHOOK - validations

    public function test_webhook_with_invalid_payment_id(): void
    {
        $response = $this->postJson('/api/payments/webhook', [
            'payment_id' => 99999,
        ]);

        $response->assertStatus(422);
    }

    // WEBHOOK - fields

    public function test_webhook_sets_paid_at_and_transaction_id(): void
    {
        ['payment' => $payment] = $this->createOrderWithPayment();

        $this->assertNull($payment->paid_at);
        $this->assertNull($payment->transaction_id);

        $this->postJson('/api/payments/webhook', [
            'payment_id'     => $payment->id,
            'transaction_id' => 'TXN-ABC123',
        ]);

        $payment->refresh();

        $this->assertNotNull($payment->paid_at);
        $this->assertEquals('TXN-ABC123', $payment->transaction_id);
    }
}
