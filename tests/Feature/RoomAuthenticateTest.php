<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoomAuthenticateTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_rooms(): void
    {
        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Room 1',
            'capacity' => 2,
            'value' => 100.00,
            'external_id' => 'room_1',
        ]);
        Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Room 2',
            'capacity' => 3,
            'value' => 150.00,
            'external_id' => 'room_2',
        ]);
        Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Room 3',
            'capacity' => 4,
            'value' => 200.00,
            'external_id' => 'room_3',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson('/api/rooms');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_create_room_requires_auth(): void
    {
        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $payload = [
            'hotel_id' => $hotel->id,
            'external_id' => 'room_1',
            'name' => 'Suite',
            'capacity' => 4,
            'value' => 150.00,
        ];

        $response = $this->postJson('/api/rooms', $payload);

        $response->assertStatus(401);
    }

    public function test_create_room_with_auth(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        Sanctum::actingAs($user);

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $payload = [
            'hotel_id' => $hotel->id,
            'external_id' => 'room_1',
            'name' => 'Suite',
            'capacity' => 4,
            'value' => 150.00,
        ];

        $response = $this->postJson('/api/rooms', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Suite');
    }

    public function test_show_room(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Test Room',
            'capacity' => 2,
            'value' => 100.00,
            'external_id' => 'room_1',
        ]);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson("/api/rooms/{$room->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $room->id);
    }

    public function test_update_room_requires_auth(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Test Room',
            'capacity' => 2,
            'value' => 100.00,
            'external_id' => 'room_1',
        ]);

        $payload = ['name' => 'Updated Room'];

        $response = $this->putJson("/api/rooms/{$room->id}", $payload);

        $response->assertStatus(401);
    }

    public function test_delete_room_requires_auth(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Test Room',
            'capacity' => 2,
            'value' => 100.00,
            'external_id' => 'room_1',
        ]);

        $response = $this->deleteJson("/api/rooms/{$room->id}");

        $response->assertStatus(401);
    }
}
