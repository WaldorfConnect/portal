<?php

namespace App\Helpers;

use App\Exceptions\LDAPException;
use App\Models\GroupModel;
use App\Models\RegionModel;
use LdapRecord\Models\OpenLDAP\Group;
use LdapRecord\Models\OpenLDAP\OrganizationalUnit;

function createGroupModel(Group $ldapGroup): GroupModel
{
    return new GroupModel($ldapGroup->cn[0], $ldapGroup->members()->get()->toArray());
}

/**
 * @return RegionModel[]
 * @throws LDAPException
 */
function getGroups(): array
{
    return getRegionsAndGroupsIn(getenv('ldap.groupsDN'));
}

/**
 * @return RegionModel[]
 * @throws LDAPException
 */
function getSchools(): array
{
    return getRegionsAndGroupsIn(getenv('ldap.schoolsDN'));
}

/**
 * @return RegionModel[]
 * @throws LDAPException
 */
function getRegionsAndGroupsIn(string $dn): array
{
    openLDAPConnection();
    $regions = [];
    $ldapRegions = OrganizationalUnit::query()->in($dn)->get();
    $ldapRegions->shift();

    foreach ($ldapRegions as $ldapRegion) {
        $groups = [];
        $ldapGroups = Group::query()->in($ldapRegion->getDN())->get();
        foreach ($ldapGroups as $ldapGroup) {
            $groups[] = createGroupModel($ldapGroup);
        }

        $regions[] = new RegionModel($ldapRegion->ou[0], $ldapRegion->ou[1], $groups);
    }

    return $regions;
}