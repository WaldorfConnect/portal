<?php

namespace App\Entities;

enum MembershipStatus: string
{
    case PENDING = "PENDING";
    case USER = "USER";
    case ADMIN = "ADMIN";
}