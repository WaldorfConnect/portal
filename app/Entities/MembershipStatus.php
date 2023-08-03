<?php

namespace App\Entities;

enum MembershipStatus: string
{
    case PENDING = "PENDING";
    case USER = "USER";
    case ADMIN = "ADMIN";

    public function badge(): ?string
    {
        return match ($this) {
            self::PENDING => '<span class="badge bg-warning">Ausstehend</span>',
            self::USER => '<span class="badge bg-success">Mitglied</span>',
            self::ADMIN => '<span class="badge bg-danger">Administrator</span>',
        };
    }
}