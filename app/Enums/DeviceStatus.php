<?php

namespace App\Enums;

enum DeviceStatus: string
{
    use Traits\ToArray;

    case INITIATED = 'initiated';
    case CONNECTED = 'connected';
    case INTERRUPT = 'interrupt';
}
