<?php

namespace App\Enums;

enum AssignmentStatus: string
{
    case Proposed = 'proposed';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
}
