<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Fee;
use App\Models\Reserve;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class ReserveService
{
    public function checkAvailability(int $roomId, Carbon $checkIn, Carbon $checkOut): bool
    {
        $overlappingReserves = Reserve::where('room_id', $roomId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                });
            })
            ->count();

        return $overlappingReserves === 0;
    }

    //Lógica de Cupons

    public function findValidCoupon(string $code): ?Coupon
    {
        $coupon = Coupon::where('code', $code)
            ->where('active', true)  //
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->lockForUpdate()
            ->first();

        return $coupon;
    }


    public function deactivateCoupon(Coupon $coupon): void
    {
        $coupon->update(['active' => false]);
    }

    public function calculateDiscount(Coupon $coupon, float $total): float
    {
        if ($coupon->type === 'percentage') {
            return round(($total * $coupon->value) / 100,2);
        }

        return  round(min($coupon->value, $total));
    }

    public function calculateReserveTotals(float $total, ?string $couponCode = null): array
    {
        $couponId = null;
        $discount = 0.0;
        $coupon = null;

        if ($couponCode) {
            $coupon = $this->findValidCoupon($couponCode);

            if ($coupon === null) {
                throw ValidationException::withMessages([
                    'coupon_code' => 'Cupom inexistente, inativo ou fora da validade.',
                ]);
            }

            if (!$coupon->canApplyTo($total)) {
                throw ValidationException::withMessages([
                    'coupon_code' => $coupon->getMinimumPurchaseMessage(),
                ]);
            }

            $discount = $this->calculateDiscount($coupon, $total);

        }

        $fee = $this->calculateFees($total);
        $finalTotal = $total - $discount + $fee;

        return [
            'coupon_id' => $couponId,
            'coupon' => $coupon,
            'discount_total' => $discount,
            'fee_total' => $fee,
            'final_total' => $finalTotal,
        ];
    }

    public function calculateFees(float $total): float
    {
        $fees = Fee::where('active', true)->get();

        $feeAmount = 0.0;

        foreach ($fees as $fee) {
            if ($fee->type === 'percentage') {
                $feeAmount += ($total * $fee->value) / 100;
            } else {
                $feeAmount += $fee->value;
            }
        }

        return $feeAmount;
    }

    public function createReserve(array $data): Reserve
    {
        return DB::transaction(function () use ($data) {
            if (!$this->checkAvailability(
                $data['room_id'],
                Carbon::parse($data['check_in']),
                Carbon::parse($data['check_out'])
            )) {
                throw ValidationException::withMessages([
                    'room_id' => 'Quarto indisponível no período informado.',
                ]);
            }

            $totals = $this->calculateReserveTotals(
                (float) $data['total'],
                $data['coupon_code'] ?? null
            );

            $reserve = Reserve::create([
                'external_id' => $data['external_id'],
                'hotel_id' => $data['hotel_id'],
                'room_id' => $data['room_id'],
                'guest_id' => $data['guest_id'],
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'total' => $data['total'],
                'coupon_id' => $totals['coupon_id'],
                'discount_total' => $totals['discount_total'],
                'fee_total' => $totals['fee_total'],
                'final_total' => $totals['final_total'],
            ]);

            if ($totals['coupon'] !== null) {
                $this->deactivateCoupon($totals['coupon']);
            }

            return $reserve;
        });
    }
}
