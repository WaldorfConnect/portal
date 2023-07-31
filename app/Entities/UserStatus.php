<?php

namespace App\Entities;

enum UserStatus: string
{
    case OK = "OK";
    case PENDING_REGISTER = "PENDING_REGISTER";
    case PENDING_ACCEPT = "PENDING_ACCEPT";
    case PENDING_EMAIL = "PENDING_EMAIL";
    case PENDING_PWRESET = "PENDING_PWRESET";
}