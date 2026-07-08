<?php

namespace App\Enums;

enum JourneyPreference: string
{
    case Short = 'short';
    case Long = 'long';
    case Any = 'any';
}
