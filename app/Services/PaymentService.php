<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;

class PaymentService
{
    public function canUserManageHotelPayments(User $user, Hotel $hotel): bool
    {
        if ($user->hotel_id !== $hotel->id) {
            return false;
        }

        return $user->canManagePayments();
    }

    public function listPaymentsByHotel(Hotel $hotel): Collection
    {
        return Payment::where('hotel_id', $hotel->id)
            ->with('reserve')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findPaymentByHotel(Hotel $hotel, int $paymentId): Payment
    {
        return Payment::where('hotel_id', $hotel->id)
            ->with('reserve')
            ->firstOrFail($paymentId);
    }

    public function updatePayment(array $data, Hotel $hotel, int $paymentId, User $updater): Payment
    {
        $payment = $this->findPaymentByHotel($hotel, $paymentId);

        if (!$this->canUserManageHotelPayments($updater, $hotel)) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                'Sem permissão para atualizar pagamentos neste hotel.'
            );
        }

        $validated = validator($data, [
            'status' => ['required', Rule::in([
                'pending',
                'paid',
                'refunded',
                'failed',
            ])],

            'external_reference' => ['nullable', 'string', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ])->validate();

        $payment->status = $validated['status'];

        if (isset($validated['external_reference'])) {
            $payment->external_reference = $validated['external_reference'];
        }

        if (isset($validated['metadata'])) {
            $payment->metadata = $validated['metadata'];
        }

        if ($payment->status === 'paid' && $payment->paid_at === null) {
            $payment->paid_at = now();
        }

        $payment->save();

        $payment->load('reserve');

        return $payment;
    }
}
