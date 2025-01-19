<?php

namespace App\Helpers;

use App\Entities\Region;
use App\Entities\Search\SearchEntry;
use App\Entities\Search\SearchEntryType;
use App\Models\RegionModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

function getSearchEntries(string $query): array
{
    $entries = [];

    $groups = getGroupsByName($query);
    foreach ($groups as $group) {
        $entries[] = new SearchEntry(
            $group->getName(),
            'Gruppe',
            [
                '<i class="fas fa-arrow-up-right-from-square"></i> Öffnen' => base_url("group/{$group->getId()}"),
            ]
        );
    }

    $users = getUsersByName($query);
    foreach ($users as $user) {
        if (!$user->isActive())
            continue;

        $entries[] = new SearchEntry(
            $user->getName(),
            'Benutzer',
            [
                '<i class="fas fa-arrow-up-right-from-square"></i> Profil anzeigen' => base_url("user/{$user->getUsername()}"),
                '<i class="fas fa-message"></i> Chat starten' => "https://cloud.waldorfconnect.de/apps/spreed/?callUser={$user->getUsername()}"
            ]
        );
    }

    return $entries;
}