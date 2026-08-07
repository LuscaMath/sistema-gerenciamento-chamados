<?php

namespace App;

enum UserRole: string
{
    case REQUESTER = 'requester';
    case TECHNICIAN = 'technician';
    CASE ADMIN = 'admin';
}
