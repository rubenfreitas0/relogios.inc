<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestHelpers;

class AddressTest extends TestCase
{
    use RefreshDatabase, TestHelpers;

    // LISTAR MORADAS

    public function test_can_list_addresses(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();

        Address::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/addresses', $headers);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    // CRIAR MORADA

    public function test_can_create_address(): void
    {
        ['headers' => $headers] = $this->createUser();

        $response = $this->postJson('/api/addresses', [
            'firstname'     => 'João',
            'lastname'      => 'Silva',
            'phone'         => '912345678',
            'address_line1' => 'Rua de Teste 123',
            'city'          => 'Lisboa',
            'postal_code'   => '1000-001',
            'country'       => 'PT',
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonPath('data.firstname', 'João')
            ->assertJsonPath('data.city', 'Lisboa');
    }

    public function test_first_address_is_default(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();

        // User não tem moradas — a primeira deve ficar default
        $response = $this->postJson('/api/addresses', [
            'firstname'     => 'João',
            'lastname'      => 'Silva',
            'address_line1' => 'Rua A',
            'city'          => 'Lisboa',
            'postal_code'   => '1000-001',
        ], $headers);

        $response->assertStatus(201)
            ->assertJsonPath('data.is_default', true);

        // A segunda NÃO deve ficar default automaticamente
        $response2 = $this->postJson('/api/addresses', [
            'firstname'     => 'Maria',
            'lastname'      => 'Santos',
            'address_line1' => 'Rua B',
            'city'          => 'Porto',
            'postal_code'   => '4000-001',
        ], $headers);

        $response2->assertStatus(201)
            ->assertJsonPath('data.is_default', false);
    }

    // VER DETALHE

    public function test_can_show_address(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/addresses/' . $address->id, $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $address->id);
    }

    public function test_cannot_show_other_users_address(): void
    {
        ['user' => $userA] = $this->createUser();
        ['headers' => $headersB] = $this->createUser(['email' => 'outro@teste.com']);

        $address = Address::factory()->create(['user_id' => $userA->id]);

        $response = $this->getJson('/api/addresses/' . $address->id, $headersB);

        $response->assertStatus(403);
    }

    // ATUALIZAR MORADA

    public function test_can_update_address(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $address = Address::factory()->create([
            'user_id' => $user->id,
            'city'    => 'Lisboa',
        ]);

        $response = $this->putJson('/api/addresses/' . $address->id, [
            'city' => 'Porto',
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.city', 'Porto');
    }

    public function test_setting_default_unsets_previous(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();

        $address1 = Address::factory()->create([
            'user_id'    => $user->id,
            'is_default' => true,
        ]);
        $address2 = Address::factory()->create([
            'user_id'    => $user->id,
            'is_default' => false,
        ]);

        // Marcar a segunda como default via update
        $response = $this->putJson('/api/addresses/' . $address2->id, [
            'is_default' => true,
        ], $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_default', true);

        // A primeira já não deve ser default
        $this->assertFalse($address1->fresh()->is_default);
    }

    // SET DEFAULT

    public function test_can_set_default(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();

        $address1 = Address::factory()->create([
            'user_id'    => $user->id,
            'is_default' => true,
        ]);
        $address2 = Address::factory()->create([
            'user_id'    => $user->id,
            'is_default' => false,
        ]);

        // Usar endpoint dedicado PATCH /addresses/{id}/default
        $response = $this->patchJson('/api/addresses/' . $address2->id . '/default', [], $headers);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_default', true);

        // A anterior já não é default
        $this->assertFalse($address1->fresh()->is_default);
    }

    // ELIMINAR MORADA

    public function test_can_delete_address(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson('/api/addresses/' . $address->id, [], $headers);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_deleting_default_promotes_next(): void
    {
        ['user' => $user, 'headers' => $headers] = $this->createUser();

        $address1 = Address::factory()->create([
            'user_id'    => $user->id,
            'is_default' => true,
        ]);
        $address2 = Address::factory()->create([
            'user_id'    => $user->id,
            'is_default' => false,
        ]);

        // Apagar a default
        $this->deleteJson('/api/addresses/' . $address1->id, [], $headers);

        // A próxima deve ser promovida
        $this->assertTrue($address2->fresh()->is_default);
    }

    // ACESSO CRUZADO

    public function test_cannot_manage_other_users_addresses(): void
    {
        ['user' => $userA] = $this->createUser();
        ['headers' => $headersB] = $this->createUser(['email' => 'outro@teste.com']);

        $address = Address::factory()->create(['user_id' => $userA->id, 'city' => 'Lisboa']);

        // Não pode atualizar
        $this->putJson('/api/addresses/' . $address->id, ['city' => 'Hacked'], $headersB)
            ->assertStatus(403);

        // Não pode apagar
        $this->deleteJson('/api/addresses/' . $address->id, [], $headersB)
            ->assertStatus(403);

        // Confirmar que os dados não mudaram
        $this->assertEquals('Lisboa', $address->fresh()->city);
    }
}
