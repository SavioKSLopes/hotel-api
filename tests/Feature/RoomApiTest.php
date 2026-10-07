<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithHotel(): array
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TEST-001',
            'name' => 'Hotel Test',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        return ['hotel' => $hotel, 'user' => $user];
    }

    public function test_it_lists_rooms_with_pagination(): void
    {
        $creds = $this->createUserWithHotel();
        $hotel = $creds['hotel'];

        Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-LIST-001',
            'name' => 'Quarto de Listagem 1',
        ]);

        Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-LIST-002',
            'name' => 'Quarto de Listagem 2',
        ]);

        $response = $this->actingAs($creds['user'])->getJson('/api/rooms');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Quarto de Listagem 1')
            ->assertJsonPath('data.1.name', 'Quarto de Listagem 2')
            ->assertJsonStructure([
                'data',
                'links',
                'meta',
            ]);
    }

    public function test_it_creates_a_room(): void
    {
        $creds = $this->createUserWithHotel();
        $hotel = $creds['hotel'];

        $response = $this->actingAs($creds['user'])->postJson('/api/rooms', [
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-CREATE-001',
            'name' => 'Quarto de Criação',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.hotel_id', $hotel->id)
            ->assertJsonPath('data.external_id', 'ROOM-CREATE-001')
            ->assertJsonPath('data.name', 'Quarto de Criação');

        $this->assertDatabaseHas('rooms', [
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-CREATE-001',
            'name' => 'Quarto de Criação',
        ]);
    }

    public function test_it_requires_room_creation_fields(): void
    {
        $creds = $this->createUserWithHotel();

        $response = $this->actingAs($creds['user'])->postJson('/api/rooms', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'hotel_id',
                'external_id',
                'name',
            ]);

        $this->assertDatabaseCount('rooms', 0);
    }

    public function test_it_shows_a_room(): void
    {
        $creds = $this->createUserWithHotel();
        $hotel = $creds['hotel'];

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-SHOW-001',
            'name' => 'Quarto de Consulta',
        ]);

        $response = $this->actingAs($creds['user'])->getJson("/api/rooms/{$room->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $room->id)
            ->assertJsonPath('data.hotel_id', $hotel->id)
            ->assertJsonPath('data.external_id', 'ROOM-SHOW-001')
            ->assertJsonPath('data.name', 'Quarto de Consulta');
    }

    public function test_it_updates_a_room(): void
    {
        $creds = $this->createUserWithHotel();
        $hotel = $creds['hotel'];

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-UPDATE-001',
            'name' => 'Quarto Antigo',
        ]);

        $response = $this->actingAs($creds['user'])->patchJson("/api/rooms/{$room->id}", [
            'name' => 'Quarto Atualizado',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $room->id)
            ->assertJsonPath('data.name', 'Quarto Atualizado');

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Quarto Atualizado',
        ]);
    }

    public function test_it_deletes_a_room(): void
    {
        $creds = $this->createUserWithHotel();
        $hotel = $creds['hotel'];

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-DELETE-001',
            'name' => 'Quarto para Excluir',
        ]);

        $response = $this->actingAs($creds['user'])->deleteJson("/api/rooms/{$room->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }
}
