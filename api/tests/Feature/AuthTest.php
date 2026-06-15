<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class AuthTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // ─────────────────────────────────────────────
    // REGISTER
    // ─────────────────────────────────────────────

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'firstname'             => 'João',
            'lastname'              => 'Silva',
            'email'                 => 'joao@teste.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'phone'                 => '912345678',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'firstname', 'lastname', 'email'],
                'token',
                'message',
            ]);

        $this->assertDatabaseHas('users', [
            'email'     => 'joao@teste.com',
            'firstname' => 'João',
        ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'duplicado@teste.com']);

        $response = $this->postJson('/api/register', [
            'firstname'             => 'Maria',
            'lastname'              => 'Santos',
            'email'                 => 'duplicado@teste.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_fails_with_invalid_data(): void
    {
        // Password demasiado curta + campos obrigatórios em falta
        $response = $this->postJson('/api/register', [
            'email'                 => 'bad@teste.com',
            'password'              => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['firstname', 'lastname', 'password']);
    }

    // ─────────────────────────────────────────────
    // LOGIN
    // ─────────────────────────────────────────────

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email'    => 'login@teste.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'login@teste.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'firstname', 'lastname', 'email'],
                'token',
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'wrong@teste.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'wrong@teste.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Credenciais inválidas']);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->create([
            'email'     => 'inativo@teste.com',
            'password'  => 'password123',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'inativo@teste.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Conta desativada']);
    }

    // ─────────────────────────────────────────────
    // LOGOUT
    // ─────────────────────────────────────────────

    public function test_user_can_logout(): void
    {
        ['headers' => $headers, 'user' => $user] = $this->createUser();

        $response = $this->postJson('/api/logout', [], $headers);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Sessão terminada com sucesso.']);

        // Verificar que o token foi revogado
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    // ─────────────────────────────────────────────
    // PROTEÇÃO DE ROTAS
    // ─────────────────────────────────────────────

    public function test_guest_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }
}
