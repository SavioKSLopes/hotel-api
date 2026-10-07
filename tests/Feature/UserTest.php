<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_users(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $authUser = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        Sanctum::actingAs($authUser);

        User::create([
            'name' => 'User 1',
            'email' => 'user1@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        User::create([
            'name' => 'User 2',
            'email' => 'user2@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        User::create([
            'name' => 'User 3',
            'email' => 'user3@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/hotels/{$hotel->id}/users");

        $response->assertStatus(200)
            ->assertJsonCount(4, 'data');
    }

    public function test_create_user(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $authUser = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        Sanctum::actingAs($authUser);

        $payload = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'manager',
        ];

        $response = $this->postJson("/api/hotels/{$hotel->id}/users", $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test User')
            ->assertJsonPath('data.email', 'test@example.com');
    }

    public function test_show_user(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);
        $authUser = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        Sanctum::actingAs($authUser);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/hotels/{$hotel->id}/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_update_user(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);
        $authUser = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        Sanctum::actingAs($authUser);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/hotels/{$hotel->id}/users/{$user->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.email', 'updated@example.com');
    }

    public function test_delete_user(): void
    {

        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);
        $authUser = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);
        Sanctum::actingAs($authUser);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'hotel_id' => $hotel->id,
            'role' => 'manager',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/hotels/{$hotel->id}/users/{$user->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
