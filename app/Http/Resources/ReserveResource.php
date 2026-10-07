<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReserveResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'external_id' => $this->external_id,
            'hotel_id' => $this->hotel_id,
            'room_id' => $this->room_id,
            'guest_id' => $this->guest_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'total' => $this->total,
        ];
    }
}
