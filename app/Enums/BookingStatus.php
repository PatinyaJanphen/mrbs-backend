<?php

namespace App\Enums;

enum BookingStatus: int
{
    case PENDING = 0;
    case APPROVED = 1;
    case REJECTED = 2; // Can also mean cancelled depending on context, we'll keep it as 2

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected/Cancelled',
        };
    }
}
