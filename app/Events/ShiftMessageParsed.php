<?php

namespace App\Events;

use App\Models\ShiftMessage;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShiftMessageParsed
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly ShiftMessage $shiftMessage) {}
}
