<?php

namespace App\Entities\Search;

enum SearchEntryType: string
{
    case ORGANISATION = "ORGANISATION";
    case USER = "USER";

    public function badge(): ?string
    {
        return match ($this) {
            self::ORGANISATION => '<span class="badge bg-success">Organisation</span>',
            self::USER => '<span class="badge bg-success">Benutzer</span>'
        };
    }
}