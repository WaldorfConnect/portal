<?php

namespace App\Helpers;

use App\Entities\LDAPOrganisation;
use App\Entities\Membership;
use App\Entities\Organisation;
use App\Entities\User;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use LdapRecord\LdapRecordException;
use LdapRecord\Query\Collection;
use ReflectionException;

function syncLDAPUsers(): void
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
                CLI::write('Updated user ' . $wantedUser->getUsername() . ' on LDAP server');
            } catch (LdapRecordException $e) {
                CLI::error($e->getMessage());
            }
        } else {
            try {
                $uid = $ldapUser->uid[0];
                // Delete from LDAP server if no longer in database
                $ldapUser->delete();
                CLI::write('Deleted user ' . $uid . ' on LDAP server');
            } catch (LdapRecordException $e) {
                CLI::error($e->getMessage());
            }
        }
    }

    foreach ($users as $user) {
        try {
            // Create user on LDAP server is inexistent
            createLDAPUser($user);
            CLI::write('Created user ' . $user->getUsername() . ' on LDAP server');
        } catch (LdapRecordException $e) {
            CLI::error($e->getMessage());
        }
    }
}

function syncLDAPOrganisations(): void
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
                CLI::write('Updated organisation ' . $wantedOrganisation->getDisplayName() . ' on LDAP server');
            } catch (LdapRecordException $e) {
                CLI::error($e->getTraceAsString());
            }
        } else {
            try {
                $displayName = $ldapOrganisation->cn[0];

                // Delete from LDAP server if no longer in database
                $ldapOrganisation->delete();
                CLI::write('Deleted organisation ' . $displayName . ' on LDAP server');
            } catch (LdapRecordException $e) {
                CLI::error($e->getTraceAsString());
            }
        }
    }

    foreach ($organisations as $organisation) {
        try {
            // Create user on LDAP server is inexistent
            createLDAPOrganisation($organisation);
            CLI::write('Created organisation ' . $organisation->getDisplayName() . ' on LDAP server');
        } catch (LdapRecordException $e) {
            CLI::error($e->getTraceAsString());
        }
    }
}

function syncOrganisationFolders(): void
{
    $client = new Client([
        RequestOptions::VERIFY => CaBundle::getSystemCaRootBundlePath(),
        RequestOptions::AUTH => [
            getenv('nextcloud.username'),
            getenv('nextcloud.password')
        ],
        RequestOptions::HEADERS => [
            'Accept' => 'application/json',
            'OCS-APIRequest' => 'true'
        ]
    ]);

    $organisations = getOrganisations();

    foreach (getOrganisationFolders($client) as $folder) {
        // Skip folders starting with underscore
        if (str_starts_with($folder->mount_point, "_"))
            continue;

        $wantedOrganisation = null;
        $wantedOrganisationIndex = 0;

        // Find database organisation, and it's array index by id of organisation on LDAP server
        foreach ($organisations as $index => $organisation) {
            if (!is_null($organisation->getFolderId()) && $organisation->getFolderId() == $folder->id) {
                $wantedOrganisationIndex = $index;
                $wantedOrganisation = $organisation;
                break;
            }
        }

        if ($wantedOrganisation) {
            unset($organisations[$wantedOrganisationIndex]);

            // Update folder on Nextcloud
            updateOrganisationFolder($client, $wantedOrganisation, $folder);
            CLI::write('Updated folder ' . $wantedOrganisation->getDisplayName() . ' on Nextcloud');
        } else {
            // Delete folder on Nextcloud
            deleteOrganisationFolder($client, $folder->id);
            CLI::write('Deleted folder ' . $folder->mount_point . ' on Nextcloud');
        }
    }

    foreach ($organisations as $organisation) {
        // Create new folder on Nextcloud
        $id = createOrganisationFolder($client, $organisation);

        if ($id != -1) {
            try {
                $organisation->setFolderId($id);
                saveOrganisation($organisation);

                CLI::write('Created folder ' . $organisation->getDisplayName() . ' on Nextcloud');
            } catch (DatabaseException|ReflectionException) {
            }
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
}

function createOrganisationFolder(Client $client, Organisation $organisation): int
{
    try {
        $response = $client->post(FOLDERS_API . '/folders', [
            'json' => [
                'mountpoint' => $organisation->getFolderMountPoint()
            ]
        ]);

        $decodedResponse = json_decode($response->getBody()->getContents());
        $data = $decodedResponse->ocs->data;

        $client->post(FOLDERS_API . '/folders/' . $data->id . '/groups', [
            'json' => [
                'group' => $organisation->getDisplayName()
            ]
        ]);

        return $data->id;
    } catch (GuzzleException $e) {
        CLI::error("Error while creating folder: " . $e->getMessage());
    }

    return -1;
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

function updateOrganisationFolder(Client $client, Organisation $organisation, object $folder): void
{
    try {
        $client->post(FOLDERS_API . '/folders/' . $organisation->getFolderId() . '/mountpoint', [
            'json' => [
                'mountpoint' => $organisation->getFolderMountPoint()
            ]
        ]);
    } catch (GuzzleException $e) {
        CLI::error("Error while creating folder: " . $e->getMessage());
    }

    // Add group as member of folder
    $groups = $folder->groups;
    if (empty($groups) || !array_key_exists($organisation->getDisplayName(), get_object_vars($groups))) {
        try {
            $client->post(FOLDERS_API . '/folders/' . $organisation->getFolderId() . '/groups', [
                'json' => [
                    'group' => $organisation->getDisplayName()
                ]
            ]);
        } catch (GuzzleException $e) {
            CLI::error("Error while adding group: " . $e->getMessage());
        }
    }
}

function deleteOrganisationFolder(Client $client, int $id): void
{
    try {
        $client->delete(FOLDERS_API . '/folders/' . $id);
    } catch (GuzzleException $e) {
        CLI::error("Error while deleting folder: " . $e->getMessage());
    }
}

/**
 * @return array|Collection
 */
function getLDAPUsers(): array|Collection
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
 * @return array|Collection
 */
function getLDAPOrganisations(): array|Collection
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

function getOrganisationFolders(Client $client): array
{
    $dataArray = [];
    try {
        $response = $client->get(FOLDERS_API . '/folders');
        $decodedResponse = json_decode($response->getBody()->getContents());
        $data = $decodedResponse->ocs->data;

        return get_object_vars($data);
    } catch (GuzzleException $e) {
        CLI::error("Error while requesting folders: " . $e->getMessage());
    }
    return $dataArray;
}