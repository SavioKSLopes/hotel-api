<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['sometimes', 'integer', 'exists:hotels,id'],

            'external_id' => ['sometimes', 'string', 'max:50'],

            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
