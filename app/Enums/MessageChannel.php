<?php

namespace App\Enums;

enum MessageChannel: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Whatsapp = 'whatsapp';
    case Manual = 'manual';
}
