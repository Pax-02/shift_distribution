<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

/**
 * Validates a parser's output before anything is persisted. This is a
 * standalone validator, not a Form Request, because it runs mid-job against
 * AI-generated data — there's no HTTP request to hang a FormRequest off of.
 */
class ParsedMessageValidator
{
    public function validate(ParsedShiftMessageDTO $dto): MessageBag
    {
        return Validator::make($dto->toArray(), [
            'client' => ['required', 'string'],
            'date' => ['required', 'date'],
            'pickup' => ['required', 'string'],
            'destination' => ['required', 'string'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'gender_requirement' => ['required', 'in:any,male,female'],
        ])->errors();
    }

    public function passes(ParsedShiftMessageDTO $dto): bool
    {
        return $this->validate($dto)->isEmpty();
    }
}
