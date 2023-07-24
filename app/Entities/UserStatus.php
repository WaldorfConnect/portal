<?php

namespace App\Entities;

enum UserStatus: string
{
    case PENDING_ACCEPT = "PENDING_ACCEPT";
    case PENDING_EMAIL = "PENDING_EMAIL";
    case USER = "USER";
    case ADMIN = "ADMIN";
}