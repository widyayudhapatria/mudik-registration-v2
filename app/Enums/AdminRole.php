<?php

namespace App\Enums;

enum AdminRole: string
{
    use EnumTraits;

    case SuperAdmin = 'super_admin';
    case Validator = 'validator';
    case Scanner = 'scanner';
}