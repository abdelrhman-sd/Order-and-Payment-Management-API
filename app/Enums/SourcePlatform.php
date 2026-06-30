<?php

namespace App\Enums;

enum SourcePlatform: string
{
    case WEB = 'web';
    case IOS = 'ios';
    case ANDROID = 'android';
}
