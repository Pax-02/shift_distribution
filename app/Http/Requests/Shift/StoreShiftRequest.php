<?php

namespace App\Http\Requests\Shift;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Shift::class);
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'uuid', 'exists:clients,id'],
            'pickup_address_id' => ['nullable', 'uuid', 'exists:client_addresses,id'],
            'pickup_address_text' => ['required_without:pickup_address_id', 'nullable', 'string', 'max:255'],
            'destination_address_id' => ['nullable', 'uuid', 'exists:client_addresses,id'],
            'destination_address_text' => ['required_without:destination_address_id', 'nullable', 'string', 'max:255'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'gender_requirement' => ['sometimes', 'in:any,male,female'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
