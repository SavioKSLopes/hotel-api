<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Reserve;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private function createPaymentData(): array
    {
        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'external_id' => 'test_hotel',
        ]);

        $room = Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Test Room',
            'external_id' => 'room_1',
        ]);

        $guest = Guest::create([
            'name' => 'Test',
            'last_name' => 'Guest',
            'phone' => '11999999999',
        ]);

        $reserve = Reserve::create([
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-10-10',
            'check_out' => '2026-10-15',
            'external_id' => 'reserve_1',
            'total' => 500.00,
            'final_total' => 500.00,
        ]);

        $paymentMethod = PaymentMethod::create([
            'name' => 'Credit Card',
            'external_id' => 'credit_card',
        ]);

        return [
            'hotel' => $hotel,
            'reserve' => $reserve,
            'paymentMethod' => $paymentMethod,
        ];
    }

    private function authenticateManager(Hotel $hotel): User
    {
        $manager = new User;
        $manager->name = 'Authenticated Manager';
        $manager->email = 'manager@example.com';
        $manager->password = 'password123';
        $manager->hotel_id = $hotel->id;
        $manager->role = 'manager';
        $manager->is_active = true;
        $manager->save();

        Sanctum::actingAs($manager);

        return $manager;
    }

    public function test_list_payments(): void
    {
        $data = $this->createPaymentData();
        $this->authenticateManager($data['hotel']);

        Payment::create([
            'hotel_id' => $data['hotel']->id,
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'value' => 100.00,
            'status' => 'pending',
        ]);

        Payment::create([
            'hotel_id' => $data['hotel']->id,
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'value' => 200.00,
            'status' => 'pending',
        ]);

        Payment::create([
            'hotel_id' => $data['hotel']->id,
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'value' => 300.00,
            'status' => 'pending',
        ]);

        $response = $this->getJson(
            "/api/hotels/{$data['hotel']->id}/payments"
        );

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_create_payment(): void
    {
        $data = $this->createPaymentData();
        $this->authenticateManager($data['hotel']);

        $payload = [
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'value' => 100.50,
            'status' => 'pending',
        ];

        $response = $this->postJson(
            "/api/hotels/{$data['hotel']->id}/payments",
            $payload
        );

        $response->assertCreated()
            ->assertJsonPath('data.reserve_id', $data['reserve']->id)
            ->assertJsonPath('data.payment_method_id', $data['paymentMethod']->id)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('payments', [
            'hotel_id' => $data['hotel']->id,
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'status' => 'pending',
        ]);
    }

    public function test_show_payment(): void
    {
        $data = $this->createPaymentData();
        $this->authenticateManager($data['hotel']);

        $payment = Payment::create([
            'hotel_id' => $data['hotel']->id,
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'value' => 100.00,
            'status' => 'pending',
        ]);

        $this->assertNotNull($payment->id);
        $this->assertGreaterThan(0, $payment->id);

        $response = $this->getJson(
            "/api/hotels/{$data['hotel']->id}/payments/{$payment->id}"
        );

        $response->assertOk()
            ->assertJsonPath('data.id', $payment->id);
    }

    public function test_update_payment(): void
    {
        $data = $this->createPaymentData();
        $this->authenticateManager($data['hotel']);

        $payment = Payment::create([
            'hotel_id' => $data['hotel']->id,
            'reserve_id' => $data['reserve']->id,
            'payment_method_id' => $data['paymentMethod']->id,
            'value' => 100.00,
            'status' => 'pending',
        ]);

        $response = $this->putJson(
            "/api/hotels/{$data['hotel']->id}/payments/{$payment->id}",
            [
                'status' => 'paid',
                'value' => 200.00,
            ]
        );

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
            'value' => '200.00',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.value', '200.00');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);
    }
}
