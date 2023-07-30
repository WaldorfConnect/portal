<?php

namespace App\Entities;

enum UserRole: string
{
    case USER = "USER";
    case ADMIN = "ADMIN";
}