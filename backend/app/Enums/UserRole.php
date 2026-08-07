<?php

namespace App\Enums;

enum UserRole: string
{
    case REQUESTER = 'requester';
    case TECHNICIAN = 'technician';
    CASE ADMIN = 'admin';
}
