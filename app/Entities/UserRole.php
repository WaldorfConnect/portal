<?php

namespace App\Entities;

enum UserRole: string
{
    case USER = "USER";
    case SCHOOL_ADMIN = "SCHOOL_ADMIN";
    case REGION_ADMIN = "REGION_ADMIN";
    case GLOBAL_ADMIN = "GLOBAL_ADMIN";
}