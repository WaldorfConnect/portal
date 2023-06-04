<?php

namespace App\Helpers;

use App\Exceptions\AuthException;
use App\Exceptions\LDAPException;
use App\Models\GroupModel;
use App\Models\UserModel;
use LdapRecord\Models\FreeIPA\User;
use LdapRecord\Models\ModelNotFoundException;
use LdapRecord\Query\ObjectNotFoundException;

/**
 * @throws AuthException|LDAPException
 */
function isLoggedIn(): bool
{
    return !is_null(user());
}

/**
 * @throws AuthException|LDAPException
 */
function user(): ?UserModel
{
    $username = session('USER');
    if (!$username) {
        return null;
    }

    return createUserModel($username);
}

/**
 * @throws AuthException | LDAPException
 */
function createUserModel(string $username): UserModel
{
    openLDAPConnection();
    try {
        $ldapUser = (new User)->where('uid', '=', $username)->firstOrFail();
    } catch (ObjectNotFoundException|ModelNotFoundException $e) {
        throw new AuthException();
    }

    $groups = [];
    $ldapGroups = $ldapUser->groups()->recursive()->get();
    foreach ($ldapGroups as $ldapGroup) {
        $groups[] = new GroupModel($ldapGroup->getName(), $ldapGroup->getDescription(), $ldapGroup->members());
    }

    return new UserModel($ldapUser->getAttribute('uid'), $ldapUser->getAttribute('cn'), $groups);
}