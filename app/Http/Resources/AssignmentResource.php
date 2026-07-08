<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shift' => new ShiftResource($this->whenLoaded('shift')),
            'worker' => new WorkerResource($this->whenLoaded('worker')),
            'status' => $this->status?->value,
            'allocation_score' => $this->allocation_score,
            'score_breakdown' => $this->score_breakdown,
            'hours' => $this->hours,
            'assigned_at' => $this->assigned_at,
            'confirmed_at' => $this->confirmed_at,
            'cancelled_at' => $this->cancelled_at,
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at,
        ];
    }
}
