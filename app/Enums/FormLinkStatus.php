<?php

namespace App\Enums;

enum FormLinkStatus: string
{
    use EnumTraits;

    case Pending = 'pending';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
}