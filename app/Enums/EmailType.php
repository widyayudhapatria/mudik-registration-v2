<?php

namespace App\Enums;

enum EmailType: string
{
    use EnumTraits;

    case FormLink = 'form_link';
    case QrCode = 'qr_code';
    case Rejection = 'rejection';
    case SeatAllocation = 'seat_allocation';
}
