<?php

namespace App\Core\Enums;

enum ApprovalAction: string
{
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Revised = 'revised';
    case Delegated = 'delegated';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Revised => 'Returned for Revision',
            self::Delegated => 'Delegated',
        };
    }
}
