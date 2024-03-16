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

    $organisations = getOrganisationsByName($query);
    foreach ($organisations as $organisation) {
        $entries[] = new SearchEntry(
            $organisation->getName(),
            'Organisation',
            ['Öffnen' => base_url("organisation/{$organisation->getId()}")]
        );
    }

    $users = getUsersByName($query);
    foreach ($users as $user) {
        $entries[] = new SearchEntry(
            $user->getName(),
            'Benutzer',
            ['Chat starten' => "https://cloud.waldorfconnect.de/apps/spreed/?callUser={$user->getUsername()}"]
        );
    }

    return $entries;
}