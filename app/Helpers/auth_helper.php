<?php

namespace App\Helpers;

use App\Exceptions\AuthException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\LDAPException;
use App\Exceptions\MalformedCredentialsException;
use App\Exceptions\UserNotFoundException;
use App\Models\GroupModel;
use App\Models\UserModel;
use LdapRecord\Auth\PasswordRequiredException;
use LdapRecord\Auth\UsernameRequiredException;
use LdapRecord\Models\OpenLDAP\User;
use LdapRecord\Query\ObjectNotFoundException;

/**
 * @throws AuthException|LDAPException
 */
function isLoggedIn(): bool
{
    return !is_null(getCurrentUser());
}

/**
 * @throws AuthException|LDAPException
 */
function getCurrentUser(): ?UserModel
{
    $username = session('USERNAME');
    if (!$username) {
        return null;
    }

    return createUserModelByUsername($username);
}

/**
 * @throws AuthException | LDAPException
 */
function login(string $username, string $password): void
{
    try {
        $ldapUser = findLDAPUser($username);
    } catch (UserNotFoundException) {
        throw new InvalidCredentialsException();
    }

    $user = createUserModel($ldapUser);
    $connection = openLDAPConnection();
    try {
        if (!$connection->auth()->attempt($ldapUser->getDn(), $password)) {
            throw new InvalidCredentialsException();
        }
    } catch (UsernameRequiredException|PasswordRequiredException $e) {
        throw new MalformedCredentialsException($e);
    }

    session()->set('USERNAME', $user->username);
}

function logout(): void
{
    session()->remove('USERNAME');
}

/**
 * @throws AuthException|LDAPException
 */
function findLDAPUser(string $username): User
{
    openLDAPConnection();
    try {
        $ldapUser = User::query()->where('uid', '=', $username)->firstOrFail();
    } catch (ObjectNotFoundException) {
        throw new UserNotFoundException();
    }
    return $ldapUser;
}

/**
 * @throws AuthException|LDAPException
 */
function createUserModelByUsername(string $username): ?UserModel
{
    try {
        $ldapUser = findLDAPUser($username);
    } catch (UserNotFoundException) {
        return null;
    }

    return createUserModel($ldapUser);
}

function createUserModel(User $ldapUser): UserModel
{
    $groups = [];
    $ldapGroups = $ldapUser->groups()->recursive()->get();
    foreach ($ldapGroups as $ldapGroup) {
        $groups[] = new GroupModel($ldapGroup->cn[0], $ldapGroup->description[0], $ldapGroup->members()->get()->toArray());
    }

    return new UserModel($ldapUser->uid[0], $ldapUser->cn[0], $ldapUser->mail[0], $groups);
}