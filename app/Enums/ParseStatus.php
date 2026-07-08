<?php

namespace App\Enums;

enum ParseStatus: string
{
    case Pending = 'pending';
    case Parsed = 'parsed';
    case Validated = 'validated';
    case Failed = 'failed';
    case Rejected = 'rejected';
}
