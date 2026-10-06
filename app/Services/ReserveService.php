<?php

namespace App\Services;

use App\Models\Reserve;
use Carbon\Carbon;

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
}
