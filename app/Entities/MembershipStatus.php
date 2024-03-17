<?php

namespace App\Entities;

enum MembershipStatus: string
{
    case PENDING = "PENDING";
    case USER = "USER";
    case ADMIN = "ADMIN";

    public function displayName(): string
    {
        return match ($this) {
            self::PENDING => 'Anfrage ausstehend',
            self::USER => 'Mitglied',
            self::ADMIN => 'Admin',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::PENDING => '<span class="badge bg-warning"><i class="fas fa-hourglass"></i></span>',
            self::USER => '<span class="badge bg-success"><i class="fas fa-user"></i></span>',
            self::ADMIN => '<span class="badge bg-danger"><i class="fas fa-user-shield"></i></span>',
        };
    }
}