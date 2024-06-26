<?php

namespace App\Enums;

enum MessageType: string
{
    use Traits\ToArray;

    case PlainText = 'string';
    case MediaMessage = 'MessageMedia';
    case MediaFromURL = 'MessageMediaFromURL';
    case Location = 'Location';
    case Buttons = 'Buttons';
    case List = 'List';
    case Contact = 'Contact';
    case Poll = 'Poll';
}
