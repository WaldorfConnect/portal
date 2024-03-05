<?php

namespace App\Helpers;

use App\Entities\LDAPOrganisation;
use App\Entities\Membership;
use App\Entities\Organisation;
use App\Entities\User;
use CodeIgniter\CLI\CLI;
use LdapRecord\LdapRecordException;

function syncUsers(): void
{
    $users = getUsers();

    foreach (getLDAPUsers() as $ldapUser) {
        if ($ldapUser->getDN() == getPortalUserDistinguishedName()) continue;

        $wantedUser = null;
        $wantedUserIndex = 0;

        // Find database user, and it's array index by username of user on LDAP server
        foreach ($users as $index => $user) {
            if ($user->getUsername() == $ldapUser->uid[0]) {
                $wantedUserIndex = $index;
                $wantedUser = $user;
                break;
            }
        }

        if ($wantedUser) {
            // Remove from list if found
            unset($users[$wantedUserIndex]);

            try {
                // Update properties on LDAP server
                updateLDAPUser($ldapUser, $wantedUser);
                CLI::write('Updated ' . $wantedUser->getUsername() . ' on LDAP server');
            } catch (LdapRecordException $e) {
                CLI::error($e->getMessage());
            }
        } else {
            try {
                $uid = $ldapUser->uid[0];
                // Delete from LDAP server if no longer in database
                $ldapUser->delete();
                CLI::write('Deleted ' . $uid . ' on LDAP server');
            } catch (LdapRecordException $e) {
                CLI::error($e->getMessage());
            }
        }
    }

    foreach ($users as $user) {
        try {
            // Create user on LDAP server is inexistent
            createLDAPUser($user);
            CLI::write('Created ' . $user->getUsername() . ' on LDAP server');
        } catch (LdapRecordException $e) {
            CLI::error($e->getMessage());
        }
    }
}

function syncOrganisations(): void
{
    $organisations = getOrganisations();

    foreach (getLDAPOrganisations() as $ldapOrganisation) {
        $wantedOrganisation = null;
        $wantedOrganisationIndex = 0;

        // Find database organisation, and it's array index by id of organisation on LDAP server
        foreach ($organisations as $index => $organisation) {
            if ($organisation->getId() == $ldapOrganisation->uid[0]) {
                $wantedOrganisationIndex = $index;
                $wantedOrganisation = $organisation;
                break;
            }
        }

        if ($wantedOrganisation) {
            // Remove from list if found
            unset($organisations[$wantedOrganisationIndex]);

            try {
                // Update properties on LDAP server
                updateLDAPOrganisation($ldapOrganisation, $wantedOrganisation);
            } catch (LdapRecordException $e) {
                CLI::error($e->getTraceAsString());
            }
        } else {
            try {
                // Delete from LDAP server if no longer in database
                $ldapOrganisation->delete();
            } catch (LdapRecordException $e) {
                CLI::error($e->getTraceAsString());
            }
        }
    }

    foreach ($organisations as $organisation) {
        try {
            // Create user on LDAP server is inexistent
            createLDAPOrganisation($organisation);
        } catch (LdapRecordException $e) {
            CLI::error($e->getTraceAsString());
        }
    }
}

/**
 * @throws LdapRecordException
 */
function createLDAPUser(User $user): void
{
    $ldapUser = new \LdapRecord\Models\OpenLDAP\User([
        'uid' => $user->getUsername()
    ]);
    $ldapUser->inside(getUserDistinguishedName());
    $ldapUser->setDn('uid=' . $user->getUsername() . ',' . getUserDistinguishedName());

    updateLDAPUser($ldapUser, $user);
}

/**
 * @throws LdapRecordException
 */
function updateLDAPUser(\LdapRecord\Models\OpenLDAP\User $ldapUser, User $user): void
{
    $ldapUser->userPassword = $user->getPassword();
    $ldapUser->cn = $user->getName();
    $ldapUser->sn = $user->getLastName();
    $ldapUser->givenName = $user->getFirstName();
    $ldapUser->mail = $user->getEmail();

    if ($imageId = $user->getImageId()) {
        $webp = imagecreatefromwebp(getImagePathById($imageId));

        ob_start();
        imagejpeg($webp);
        $jpegPhoto = ob_get_clean();
        imagedestroy($webp);

        $ldapUser->jpegPhoto = $jpegPhoto;
    } elseif ($ldapUser->jpegPhoto) {
        $ldapUser->removeAttribute('jpegPhoto');
    }

    $ldapUser->save();
}

/**
 * @param Organisation $organisation
 * @return void
 * @throws LdapRecordException
 */
function createLDAPOrganisation(Organisation $organisation): void
{
    $ldapOrganisation = new LDAPOrganisation([
        'uid' => $organisation->getId(),
        'cn' => $organisation->getDisplayName(),
        'uniquemember' => getPortalUserDistinguishedName()
    ]);
    $ldapOrganisation->inside(getOrganisationsDistinguishedName());
    $ldapOrganisation->setDn('uid=' . $organisation->getId() . ',' . getOrganisationsDistinguishedName());

    updateLDAPOrganisation($ldapOrganisation, $organisation);
}

/**
 * @param LDAPOrganisation $ldapOrganisation
 * @param Organisation $organisation
 * @return void
 * @throws LdapRecordException
 */
function updateLDAPOrganisation(LDAPOrganisation $ldapOrganisation, Organisation $organisation): void
{
    $ldapOrganisation->cn = $organisation->getDisplayName();

    $memberships = $organisation->getMemberships();

    foreach ($ldapOrganisation->members()->get() as $ldapMember) {
        if ($ldapMember->getDN() == getPortalUserDistinguishedName()) continue;

        $wantedMember = null;
        $wantedMemberIndex = 0;

        // Find database organisation, and it's array index by id of organisation on LDAP server
        foreach ($memberships as $index => $membership) {
            if ($membership->getUser()->getUsername() == $ldapMember->uid[0]) {
                $wantedMemberIndex = $index;
                $wantedMember = $membership;
                break;
            }
        }

        if ($wantedMember) {
            // Remove from list if found
            unset($memberships[$wantedMemberIndex]);
        } else {
            // Remove member from LDAP server
            $ldapOrganisation->members()->detach($ldapMember);
            CLI::write('Removed ' . $ldapMember->uid[0] . ' from ' . $organisation->getDisplayName());
        }
    }

    foreach ($memberships as $membership) {
        $ldapUser = getLDAPUserByUsername($membership->getUser()->getUsername());

        // Add member to ldap server
        $ldapOrganisation->members()->attach($ldapUser);
        CLI::write('Added ' . $ldapUser->uid[0] . ' to ' . $organisation->getDisplayName());
    }

    $ldapOrganisation->save();
}

/**
 * @return array|\LdapRecord\Query\Collection
 */
function getLDAPUsers(): array|\LdapRecord\Query\Collection
{
    return \LdapRecord\Models\OpenLDAP\User::query()->in(getUserDistinguishedName())->paginate();
}

/**
 * @param string $username
 * @return ?\LdapRecord\Models\OpenLDAP\User
 */
function getLDAPUserByUsername(string $username): ?object
{
    return \LdapRecord\Models\OpenLDAP\User::query()->findBy('uid', $username);
}

/**
 * @return array|\LdapRecord\Query\Collection
 */
function getLDAPOrganisations(): array|\LdapRecord\Query\Collection
{
    return LDAPOrganisation::query()->in(getOrganisationsDistinguishedName())->paginate();
}

function getUserDistinguishedName(): string
{
    return getenv('ldap.usersDN');
}

function getOrganisationsDistinguishedName(): string
{
    return getenv('ldap.organisationsDN');
}

function getPortalUserDistinguishedName(): string
{
    return getenv('ldap.portalUserDN');
}