<?php

namespace App\Enums;

enum ScanResult: string
{
    use EnumTraits;

    case Success = 'success';
    case Failed = 'failed';
}