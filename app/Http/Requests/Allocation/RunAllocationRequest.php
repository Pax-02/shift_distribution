<?php

namespace App\Http\Requests\Allocation;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class RunAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shift = Shift::find($this->input('shift_id'));

        return $shift !== null && $this->user()->can('runAllocation', $shift);
    }

    public function rules(): array
    {
        return [
            'shift_id' => ['required', 'uuid', 'exists:shifts,id'],
        ];
    }
}
