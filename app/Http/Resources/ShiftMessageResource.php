<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'channel' => $this->channel?->value,
            'raw_message' => $this->raw_message,
            'sender_identifier' => $this->sender_identifier,
            'parsed_payload' => $this->parsed_payload,
            'parse_status' => $this->parse_status?->value,
            'parse_error' => $this->parse_error,
            'ai_model' => $this->ai_model,
            'shift_id' => $this->shift_id,
            'received_at' => $this->received_at,
            'processed_at' => $this->processed_at,
        ];
    }
}
