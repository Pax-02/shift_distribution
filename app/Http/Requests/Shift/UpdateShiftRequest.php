<?php

namespace App\Http\Requests\Shift;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('shift'));
    }

    public function rules(): array
    {
        return [
            'client_id' => ['sometimes', 'uuid', 'exists:clients,id'],
            'pickup_address_id' => ['nullable', 'uuid', 'exists:client_addresses,id'],
            'pickup_address_text' => ['nullable', 'string', 'max:255'],
            'destination_address_id' => ['nullable', 'uuid', 'exists:client_addresses,id'],
            'destination_address_text' => ['nullable', 'string', 'max:255'],
            'shift_date' => ['sometimes', 'date'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', 'after:start_time'],
            'gender_requirement' => ['sometimes', 'in:any,male,female'],
            'status' => ['sometimes', 'in:pending,allocated,confirmed,completed,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
