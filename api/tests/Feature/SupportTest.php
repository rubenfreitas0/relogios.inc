<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_submit_support_ticket(): void
    {
        $response = $this->postJson('/api/support', [
            'subject' => 'devolucao',
            'message' => 'Preciso de ajuda com a devolução do meu relógio.',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_submit_support_ticket(): void
    {
        \Illuminate\Support\Facades\Event::fake([
            \Illuminate\Mail\Events\MessageSending::class,
        ]);

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/support', [
            'subject' => 'devolucao',
            'message' => 'Preciso de ajuda com a devolução do meu relógio.',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Pedido de contacto enviado com sucesso.',
            ])
            ->assertJsonStructure([
                'message',
                'ticket' => [
                    'id',
                    'user_id',
                    'subject',
                    'message',
                    'status',
                    'type',
                    'created_at',
                    'updated_at',
                ]
            ]);

        // Verificar que o ticket foi guardado na base de dados
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'subject' => 'devolucao',
            'message' => 'Preciso de ajuda com a devolução do meu relógio.',
            'status' => 'open',
            'type' => 'devolucao',
        ]);

        // Verificar que o email foi enviado
        \Illuminate\Support\Facades\Event::assertDispatched(\Illuminate\Mail\Events\MessageSending::class);
    }

    public function test_support_ticket_requires_subject_and_message(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/support', [
            'subject' => '',
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subject', 'message']);
    }
}
