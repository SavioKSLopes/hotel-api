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
            'external_id' => ['required', 'string', 'max:50', 'unique:reserves,external_id'],
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'guest_id' => ['required', 'integer', 'exists:guests,id'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'total' => ['required', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
