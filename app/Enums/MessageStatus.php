<?php

namespace App\Enums;

enum MessageStatus: string
{
    use Traits\ToArray;

    case SCHEDULED = 'scheduled';
    case PENDING = 'pending';
    case INITIATED = 'initiated';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
}
