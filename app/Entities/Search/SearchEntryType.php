<?php

namespace App\Entities\Search;

enum SearchEntryType: string
{
    case GROUP = "GROUP";
    case USER = "USER";

    public function badge(): ?string
    {
        return match ($this) {
            self::GROUP => '<span class="badge bg-success">Gruppe</span>',
            self::USER => '<span class="badge bg-success">Benutzer</span>'
        };
    }
}