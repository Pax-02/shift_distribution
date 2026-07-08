<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->client_id,
            'pickup_address_id' => $this->pickup_address_id,
            'pickup_address_text' => $this->pickup_address_text,
            'destination_address_id' => $this->destination_address_id,
            'destination_address_text' => $this->destination_address_text,
            'shift_date' => $this->shift_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'gender_requirement' => $this->gender_requirement?->value,
            'status' => $this->status?->value,
            'source_message_id' => $this->source_message_id,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
