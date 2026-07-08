<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case Pending = 'pending';
    case Allocated = 'allocated';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
