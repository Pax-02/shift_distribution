<?php

namespace App\Http\Requests\ShiftMessage;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;

class StoreShiftMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ingesting a message is the entry point that eventually creates a
        // Shift, so it's gated by the same permission as creating one directly.
        return $this->user()->can('create', Shift::class);
    }

    public function rules(): array
    {
        return [
            'channel' => ['sometimes', 'in:email,sms,whatsapp,manual'],
            'raw_message' => ['required', 'string'],
            'sender_identifier' => ['nullable', 'string', 'max:255'],
        ];
    }
}
