<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reserve_id' => $this->reserve_id,
            'hotel_id' => $this->hotel_id,
            'payment_method_id' => $this->payment_method_id,
            'value' => $this->value,
            'status' => $this->status,
            'external_reference' => $this->external_reference,
            'metadata' => $this->metadata,
            'paid_at' => $this->paid_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'reserve' => $this->whenLoaded('reserve'),
            'payment_method' => $this->whenLoaded('paymentMethod'),
        ];
    }
}
