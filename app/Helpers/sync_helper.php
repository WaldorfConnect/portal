<?php

namespace App\Helpers;

use App\Entities\LDAPOrganisation;
use App\Entities\Membership;
use App\Entities\MembershipStatus;
use App\Entities\Group;
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

            // Update properties on LDAP server
            try {
                updateLDAPUser($ldapUser, $wantedUser);
                log_message('info', 'LDAP: Updated user ' . $wantedUser->getUsername());
            } catch (LdapRecordException $e) {
                log_message('error', 'LDAP: Unable to update user ' . $wantedUser->getUsername() . ': {exception}', ['exception' => $e]);
            }
        } else {
            $uid = $ldapUser->uid[0];

            // Delete from LDAP server
            try {
                $ldapUser->delete();
                log_message('info', 'LDAP: Deleted user ' . $uid);
            } catch (LdapRecordException $e) {
                log_message('error', 'LDAP: Unable to delete user ' . $uid . ': {exception}', ['exception' => $e]);
            }
        }
    }

    foreach ($users as $user) {
        // Create user on LDAP server
        try {
            createLDAPUser($user);

            log_message('info', 'LDAP: Created user ' . $user->getUsername());
        } catch (LdapRecordException $e) {
            log_message('error', 'LDAP: Unable to create user ' . $user->getUsername() . ': {exception}', ['exception' => $e]);
        }
    }
}

function syncLDAPOrganisations(): void
{
    $organisations = getGroups();

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

            // Update properties on LDAP server
            try {
                updateLDAPOrganisation($ldapOrganisation, $wantedOrganisation);

                log_message('info', 'LDAP: Updated organisation ' . $wantedOrganisation->getName());
            } catch (LdapRecordException $e) {
                log_message('error', 'LDAP: Unable to update organisation ' . $wantedOrganisation->getName() . ': {exception}', ['exception' => $e]);
            }
        } else {
            $displayName = $ldapOrganisation->cn[0];

            // Delete organisation on LDAP server
            try {
                $ldapOrganisation->delete();

                log_message('info', 'LDAP: Deleted organisation ' . $displayName);
            } catch (LdapRecordException $e) {
                log_message('error', 'LDAP: Unable to delete organisation ' . $displayName . ': {exception}', ['exception' => $e]);
            }
        }
    }

    // Create organisations missing on LDAP server
    foreach ($organisations as $organisation) {
        try {
            createLDAPOrganisation($organisation);
            log_message('info', 'LDAP: Created organisation ' . $organisation->getDisplayName());
        } catch (LdapRecordException $e) {
            log_message('error', 'LDAP: Unable to create organisation ' . $organisation->getDisplayName() . ': {exception}', ['exception' => $e]);
        }
    }
}

function syncOrganisationFolders(): void
{
    $client = createAPIClient();
    $organisations = getGroups();

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
            log_message('info', 'NC: Updated folder ' . $wantedOrganisation->getFolderMountPoint());
        } else {
            // Delete folder on Nextcloud
            deleteOrganisationFolder($client, $folder->id);
            log_message('info', 'NC: Deleted folder ' . $folder->mount_point);
        }
    }

    foreach ($organisations as $organisation) {
        // Create new folder on Nextcloud
        $id = createOrganisationFolder($client, $organisation);

        if ($id != -1) {
            try {
                $organisation->setFolderId($id);
                saveGroup($organisation);

                log_message('info', 'NC: Created folder ' . $organisation->getFolderMountPoint());
            } catch (DatabaseException|ReflectionException $e) {
                log_message('error', 'NC: Unable to create folder ' . $organisation->getFolderMountPoint() . ': {exception}', ['exception' => $e]);
            }
        }
    }
}

function syncOrganisationChats(): void
{
    $client = createAPIClient();
    $organisations = getGroups();

    foreach ($organisations as $organisation) {
        $members = $organisation->getMemberships();
        if (empty($members)) {
            log_message('info', 'NC: Skipping chat creation for empty organisation ' . $organisation->getDisplayName());
            continue;
        }

        $chatId = $organisation->getChatId();
        if (!$chatId) {
            $chatId = createOrganisationChat($client, $organisation->getDisplayName());

            if ($chatId) {
                try {
                    $organisation->setChatId($chatId);
                    saveGroup($organisation);

                    log_message('info', 'NC: Created chat ' . $organisation->getDisplayName());
                } catch (DatabaseException|ReflectionException $e) {
                    log_message('error', 'NC: Unable to create chat ' . $organisation->getDisplayName() . ': {exception}', ['exception' => $e]);
                }
            }
        }

        if (!$chatId) {
            log_message('debug', 'NC: No chat id after creation for ' . $organisation->getDisplayName());
            continue;
        }

        $participants = getOrganisationChatParticipants($client, $chatId);
        foreach ($participants as $participant) {
            if ($participant->actorType != 'users') {
                continue;
            }

            $attendeeId = $participant->attendeeId;
            $username = $participant->actorId;
            $user = getUserByUsername($username);
            if (!$user) {
                log_message('debug', 'NC: Invalid user ' . $username);
                continue;
            }

            $userId = $user->getId();
            $isModerator = $participant->participantType == 2;

            foreach ($members as $member) {
                if ($member->getId() != $userId) {
                    continue;
                }

                if ($member->getStatus() == MembershipStatus::ADMIN && !$isModerator) {
                    log_message('debug', 'NC: Promote ' . $user->getUsername());

                    promoteChatUser($client, $chatId, $attendeeId);
                } else if ($member->getStatus() == MembershipStatus::USER && $isModerator) {
                    log_message('debug', 'NC: Demote ' . $user->getUsername());

                    demoteChatUser($client, $chatId, $attendeeId);
                }
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
 * Updates the LDAP entry for a given user
 *
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
 * Creates a new LDAP entry for a given organisation
 *
 * @param Group $organisation
 * @return void
 * @throws LdapRecordException
 */
function createLDAPOrganisation(Group $organisation): void
{
    $ldapOrganisation = new LDAPOrganisation([
        'uid' => $organisation->getId(),
        'cn' => $organisation->getDisplayName(),
        'uniquemember' => getPortalUserDistinguishedName()
    ]);
    $ldapOrganisation->inside(getOrganisationsDistinguishedName());
    $ldapOrganisation->setDn('uid=' . $organisation->getId() . ',' . getOrganisationsDistinguishedName());
    $ldapOrganisation->save();
}

/**
 * Creates a group folder on the NC server for the given organisation
 *
 * @throws GuzzleException
 */
function createOrganisationFolder(Client $client, Group $organisation): int
{
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
}

/**
 * Updates the LDAP entry for a given organisation
 *
 * @param LDAPOrganisation $ldapOrganisation LDAP entry to be changed
 * @param Group $organisation updated organisation
 * @return void
 * @throws LdapRecordException
 */
function updateLDAPOrganisation(LDAPOrganisation $ldapOrganisation, Group $organisation): void
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
            log_message('info', 'LDAP: Removed ' . $ldapMember->uid[0] . ' from ' . $organisation->getDisplayName());
        }
    }

    foreach ($memberships as $membership) {
        $ldapUser = getLDAPUserByUsername($membership->getUser()->getUsername());

        // Add member to ldap server
        $ldapOrganisation->members()->attach($ldapUser);
        log_message('info', 'LDAP: Added ' . $ldapUser->uid[0] . ' to ' . $organisation->getDisplayName());
    }

    $ldapOrganisation->save();
}

/**
 * Updates a given organisations' NC group folder
 *
 * @throws GuzzleException
 */
function updateOrganisationFolder(Client $client, Group $organisation, object $folder): void
{
    $client->post(FOLDERS_API . '/folders/' . $organisation->getFolderId() . '/mountpoint', [
        'json' => [
            'mountpoint' => $organisation->getFolderMountPoint()
        ]
    ]);

    // Add group as member of folder
    $groups = $folder->groups;
    if (empty($groups) || !array_key_exists($organisation->getDisplayName(), get_object_vars($groups))) {
        $client->post(FOLDERS_API . '/folders/' . $organisation->getFolderId() . '/groups', [
            'json' => [
                'group' => $organisation->getDisplayName()
            ]
        ]);
    }
}

/**
 * Deletes a given organisations' NC group folder
 *
 * @throws GuzzleException
 */
function deleteOrganisationFolder(Client $client, int $id): void
{
    $client->delete(FOLDERS_API . '/folders/' . $id);
}

/**
 * @throws GuzzleException
 */
function createOrganisationChat(Client $client, string $groupName): ?string
{
    $response = $client->post(TALK_API . '/room', [
        'json' => [
            'roomType' => 2,
            'invite' => $groupName,
            'source' => 'portal'
        ]
    ]);

    $decodedResponse = json_decode($response->getBody()->getContents());
    return $decodedResponse->ocs->data->token;
}

/**
 * @throws GuzzleException
 */
function getOrganisationChatParticipants(Client $client, string $chatId): array
{
    $response = $client->get(TALK_API . '/room/' . $chatId . '/participants');
    $decodedResponse = json_decode($response->getBody()->getContents());

    return $decodedResponse->ocs->data;
}

/**
 * @throws GuzzleException
 */
function promoteChatUser(Client $client, string $chatId, int $attendeeId): void
{
    $client->post(TALK_API . '/room/' . $chatId . '/moderators', [
        'json' => [
            'attendeeId' => $attendeeId
        ]
    ]);
}

/**
 * @throws GuzzleException
 */
function demoteChatUser(Client $client, string $chatId, int $attendeeId): void
{
    $client->delete(TALK_API . '/room/' . $chatId . '/moderators', [
        'json' => [
            'attendeeId' => $attendeeId
        ]
    ]);
}

/**
 * Returns an HTTP client for the Nextcloud REST-API
 *
 * @return Client
 */
function createAPIClient(): Client
{
    return new Client([
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

/**
 * @throws GuzzleException
 */
function getOrganisationFolders(Client $client): array
{
    $response = $client->get(FOLDERS_API . '/folders');
    $decodedResponse = json_decode($response->getBody()->getContents());
    $data = $decodedResponse->ocs->data;

    return get_object_vars($data);
}