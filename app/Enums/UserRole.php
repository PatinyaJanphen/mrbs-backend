<?php

namespace App\Enums;

enum UserRole: int
{
    case SUPER_ADMIN = 0;
    case ADMIN = 1;
    case STAFF = 2;
    case USER = 3;
}
