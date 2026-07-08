<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender?->value,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->city,
                'postcode' => $this->postcode,
                'country' => $this->country,
                'latitude' => $this->home_latitude,
                'longitude' => $this->home_longitude,
            ],
            'contracted_weekly_hours' => $this->contracted_weekly_hours,
            'max_weekly_hours' => $this->max_weekly_hours,
            'standby' => $this->standby,
            'journey_preference' => $this->journey_preference?->value,
            'preferred_client_ids' => $this->preferred_client_ids,
            'avoided_client_ids' => $this->avoided_client_ids,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
