<?php

namespace App\Http\Requests\Worker;

use App\Models\Worker;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Worker::class);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:workers,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['required', 'in:male,female,other,prefer_not_to_say'],
            'date_of_birth' => ['nullable', 'date'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:2'],
            'home_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'home_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'contracted_weekly_hours' => ['required', 'numeric', 'min:0', 'max:168'],
            'max_weekly_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'standby' => ['boolean'],
            'journey_preference' => ['nullable', 'in:short,long,any'],
            'preferred_client_ids' => ['nullable', 'array'],
            'preferred_client_ids.*' => ['uuid', 'exists:clients,id'],
            'avoided_client_ids' => ['nullable', 'array'],
            'avoided_client_ids.*' => ['uuid', 'exists:clients,id'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
