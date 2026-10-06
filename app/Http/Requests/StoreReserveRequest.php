<?php

namespace App\Http\Requests;

use App\Services\ReserveService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreReserveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'external_id' => ['required', 'string', 'max:50'],
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'guest_id' => ['required', 'integer', 'exists:guests,id'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $checkIn = Carbon::parse($this->input('check_in'));
                $checkOut = Carbon::parse($this->input('check_out'));
                $roomId = $this->input('room_id');

                $reserveService = app(ReserveService::class);

                if (!$reserveService->checkAvailability($roomId, $checkIn, $checkOut)) {
                    $validator->errors()->add(
                        'room_id',
                        'Quarto indisponível no período informado.'
                    );
                }
            },
        ];
    }
}
