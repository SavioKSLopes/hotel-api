<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Fee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class giReserveApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_reserve(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-001',
            'name' => 'Hotel de Teste',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-001',
            'name' => 'Quarto de Teste',
        ]);

        $guest = Guest::create([
            'name' => 'João',
            'last_name' => 'Silva',
            'phone' => '11999999999',
        ]);

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-TESTE-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-13',
            'total' => 750.00,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('external_id', 'RES-TESTE-001')
            ->assertJsonPath('hotel_id', $hotel->id)
            ->assertJsonPath('room_id', $room->id)
            ->assertJsonPath('guest_id', $guest->id)
            ->assertJsonPath('total', '750.00')
            ->assertJsonPath('discount_total', '0.00')
            ->assertJsonPath('fee_total', '0.00')
            ->assertJsonPath('final_total', '750.00');

        $this->assertDatabaseHas('reserves', [
            'external_id' => 'RES-TESTE-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'total' => 750.00,
            'discount_total' => 0.00,
            'fee_total' => 0.00,
            'final_total' => 750.00,
        ]);
    }

    public function test_it_requires_external_id_and_total_to_create_a_reserve(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-002',
            'name' => 'Hotel de Validação',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-002',
            'name' => 'Quarto de Validação',
        ]);

        $guest = Guest::create([
            'name' => 'Maria',
            'last_name' => 'Oliveira',
            'phone' => '11988888888',
        ]);

        $response = $this->postJson('/api/reserves', [
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-13',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['external_id', 'total']);

        $this->assertDatabaseMissing('reserves', [
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
        ]);
    }

    public function test_it_rejects_a_reservation_when_check_out_is_not_after_check_in(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-003',
            'name' => 'Hotel de Datas',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-003',
            'name' => 'Quarto de Datas',
        ]);

        $guest = Guest::create([
            'name' => 'Carlos',
            'last_name' => 'Souza',
            'phone' => '11977777777',
        ]);

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-TESTE-DATA-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-10',
            'total' => 750.00,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['check_out']);

        $this->assertDatabaseMissing('reserves', [
            'external_id' => 'RES-TESTE-DATA-001',
        ]);
    }

    public function test_it_rejects_duplicate_external_id(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-004',
            'name' => 'Hotel de Identificador Único',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-004',
            'name' => 'Quarto de Identificador Único',
        ]);

        $guest = Guest::create([
            'name' => 'Ana',
            'last_name' => 'Costa',
            'phone' => '11966666666',
        ]);

        $this->postJson('/api/reserves', [
            'external_id' => 'RES-DUPLICADA-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-11-10',
            'check_out' => '2026-11-13',
            'total' => 750.00,
        ])->assertCreated();

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-DUPLICADA-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-12-10',
            'check_out' => '2026-12-13',
            'total' => 900.00,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['external_id']);

        $this->assertDatabaseCount('reserves', 1);
    }

    public function test_it_applies_a_valid_percentage_coupon_to_a_reserve(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-CUPOM-001',
            'name' => 'Hotel de Cupom',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-CUPOM-001',
            'name' => 'Quarto de Cupom',
        ]);

        $guest = Guest::create([
            'name' => 'Pedro',
            'last_name' => 'Almeida',
            'phone' => '11955555555',
        ]);

        $coupon = Coupon::create([
            'code' => 'DESCONTO10',
            'description' => 'Desconto de 10%',
            'type' => 'percentage',
            'value' => 10.00,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addDay()->toDateString(),
            'active' => true,
        ]);

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-CUPOM-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-11-20',
            'check_out' => '2026-11-23',
            'total' => 1000.00,
            'coupon_code' => $coupon->code,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('coupon_id', $coupon->id)
            ->assertJsonPath('discount_total', '100.00')
            ->assertJsonPath('fee_total', '0.00')
            ->assertJsonPath('final_total', '900.00');

        $this->assertDatabaseHas('reserves', [
            'external_id' => 'RES-CUPOM-001',
            'coupon_id' => $coupon->id,
            'discount_total' => 100.00,
            'fee_total' => 0.00,
            'final_total' => 900.00,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'active' => false,
        ]);
    }

    public function test_it_applies_an_active_percentage_fee_to_a_reserve(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-TAXA-001',
            'name' => 'Hotel de Taxa',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-TAXA-001',
            'name' => 'Quarto de Taxa',
        ]);

        $guest = Guest::create([
            'name' => 'Lucas',
            'last_name' => 'Ferreira',
            'phone' => '11944444444',
        ]);

        Fee::create([
            'name' => 'Taxa de serviço',
            'type' => 'percentage',
            'value' => 10.00,
            'active' => true,
        ]);

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-TAXA-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-12-01',
            'check_out' => '2026-12-04',
            'total' => 1000.00,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('coupon_id', null)
            ->assertJsonPath('discount_total', '0.00')
            ->assertJsonPath('fee_total', '100.00')
            ->assertJsonPath('final_total', '1100.00');

        $this->assertDatabaseHas('reserves', [
            'external_id' => 'RES-TAXA-001',
            'discount_total' => 0.00,
            'fee_total' => 100.00,
            'final_total' => 1100.00,
        ]);
    }

    public function test_it_calculates_total_with_percentage_coupon_and_fee(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-COMBINADO-001',
            'name' => 'Hotel de Cálculo Combinado',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-COMBINADO-001',
            'name' => 'Quarto de Cálculo Combinado',
        ]);

        $guest = Guest::create([
            'name' => 'Beatriz',
            'last_name' => 'Lima',
            'phone' => '11933333333',
        ]);

        $coupon = Coupon::create([
            'code' => 'COMBO10',
            'description' => 'Cupom de 10% para teste combinado',
            'type' => 'percentage',
            'value' => 10.00,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_until' => now()->addDay()->toDateString(),
            'active' => true,
        ]);

        Fee::create([
            'name' => 'Taxa combinada',
            'type' => 'percentage',
            'value' => 10.00,
            'active' => true,
        ]);

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-COMBINADA-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-12-10',
            'check_out' => '2026-12-13',
            'total' => 1000.00,
            'coupon_code' => $coupon->code,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('coupon_id', $coupon->id)
            ->assertJsonPath('discount_total', '100.00')
            ->assertJsonPath('fee_total', '100.00')
            ->assertJsonPath('final_total', '1000.00');

        $this->assertDatabaseHas('reserves', [
            'external_id' => 'RES-COMBINADA-001',
            'coupon_id' => $coupon->id,
            'discount_total' => 100.00,
            'fee_total' => 100.00,
            'final_total' => 1000.00,
        ]);

        $this->assertDatabaseHas('coupons', [
            'id' => $coupon->id,
            'active' => false,
        ]);
    }

    public function test_it_rejects_a_reserve_when_room_is_unavailable_for_the_period(): void
    {
        $hotel = Hotel::create([
            'external_id' => 'HOTEL-TESTE-DISPONIBILIDADE-001',
            'name' => 'Hotel de Disponibilidade',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'external_id' => 'QUARTO-TESTE-DISPONIBILIDADE-001',
            'name' => 'Quarto de Disponibilidade',
        ]);

        $guest = Guest::create([
            'name' => 'Fernanda',
            'last_name' => 'Ramos',
            'phone' => '11911111111',
        ]);

        $this->postJson('/api/reserves', [
            'external_id' => 'RES-EXISTENTE-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-12-10',
            'check_out' => '2026-12-13',
            'total' => 750.00,
        ])->assertCreated();

        $response = $this->postJson('/api/reserves', [
            'external_id' => 'RES-SOBREPOSTA-001',
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-12-11',
            'check_out' => '2026-12-14',
            'total' => 900.00,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['room_id']);

        $this->assertDatabaseCount('reserves', 1);

        $this->assertDatabaseMissing('reserves', [
            'external_id' => 'RES-SOBREPOSTA-001',
        ]);
    }
}
