<?php

namespace App\Enums;


enum PaymentStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processsing';
    case PAID       = 'paid';
    case FAILED     = 'failed';
    case REFUNDED   = 'refuned';
    case PARTIALLY_REFUNDED = 'partially_refunded';
}
