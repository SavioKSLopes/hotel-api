<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_rooms_with_pagination(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-ROOM-LIST-001',
            'name' => 'Hotel de Listagem',
        ]);

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

        $response = $this->getJson('/api/rooms');

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
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-ROOM-CREATE-001',
            'name' => 'Hotel de Criação',
        ]);

        $response = $this->postJson('/api/rooms', [
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
        $response = $this->postJson('/api/rooms', []);

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
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-ROOM-SHOW-001',
            'name' => 'Hotel de Consulta',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-SHOW-001',
            'name' => 'Quarto de Consulta',
        ]);

        $response = $this->getJson("/api/rooms/{$room->id}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $room->id)
            ->assertJsonPath('data.hotel_id', $hotel->id)
            ->assertJsonPath('data.external_id', 'ROOM-SHOW-001')
            ->assertJsonPath('data.name', 'Quarto de Consulta');
    }

    public function test_it_updates_a_room(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-ROOM-UPDATE-001',
            'name' => 'Hotel de Atualização',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-UPDATE-001',
            'name' => 'Quarto Antigo',
        ]);

        $response = $this->patchJson("/api/rooms/{$room->id}", [
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
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-ROOM-DELETE-001',
            'name' => 'Hotel de Exclusão',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'ROOM-DELETE-001',
            'name' => 'Quarto para Excluir',
        ]);

        $response = $this->deleteJson("/api/rooms/{$room->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('rooms', [
            'id' => $room->id,
        ]);
    }
}
