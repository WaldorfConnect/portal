<?php

namespace App\Helpers;

use App\Models\UserModel;
use LdapRecord\Models\OpenLDAP\User;

function createUserModel(User $ldapUser): UserModel
{
    $groups = [];
    $school = null;
    $ldapGroups = $ldapUser->groups()->recursive()->get();
    foreach ($ldapGroups as $ldapGroup) {
        $model = createGroupModel($ldapGroup);

        if (str_contains($ldapGroup->getDN(), getenv('ldap.schoolsDN'))) {
            $school = $model;
        } else {
            $groups[] = $model;
        }
    }

    return new UserModel($ldapUser->uid[0], $ldapUser->cn[0], $ldapUser->mail[0], $school, $groups);
}