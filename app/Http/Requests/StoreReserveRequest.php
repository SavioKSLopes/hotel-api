<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreReserveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
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
}
