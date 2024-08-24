<?php

namespace App\Helpers;

use App\Entities\LDAPGroup;
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

function syncLDAPGroups(): void
{
    $groups = getGroups();

    foreach (getLDAPGroups() as $ldapGroup) {
        $wantedGroup = null;
        $wantedGroupIndex = 0;

        // Find database group, and it's array index by id of group on LDAP server
        foreach ($groups as $index => $group) {
            if ($group->getId() == $ldapGroup->uid[0]) {
                $wantedGroupIndex = $index;
                $wantedGroup = $group;
                break;
            }
        }

        if ($wantedGroup) {
            // Remove from list if found
            unset($groups[$wantedGroupIndex]);

            // Update properties on LDAP server
            try {
                updateLDAPGroup($ldapGroup, $wantedGroup);

                log_message('info', 'LDAP: Updated group ' . $wantedGroup->getName());
            } catch (LdapRecordException $e) {
                log_message('error', 'LDAP: Unable to update group ' . $wantedGroup->getName() . ': {exception}', ['exception' => $e]);
            }
        } else {
            $displayName = $ldapGroup->cn[0];

            // Delete group on LDAP server
            try {
                $ldapGroup->delete();

                log_message('info', 'LDAP: Deleted group ' . $displayName);
            } catch (LdapRecordException $e) {
                log_message('error', 'LDAP: Unable to delete group ' . $displayName . ': {exception}', ['exception' => $e]);
            }
        }
    }

    // Create groups missing on LDAP server
    foreach ($groups as $group) {
        try {
            createLDAPGroup($group);
            log_message('info', 'LDAP: Created group ' . $group->getDisplayName());
        } catch (LdapRecordException $e) {
            log_message('error', 'LDAP: Unable to create group ' . $group->getDisplayName() . ': {exception}', ['exception' => $e]);
        }
    }
}

function syncGroupFolders(): void
{
    $client = createAPIClient();
    $groups = getGroups();

    foreach (getGroupFolders($client) as $folder) {
        // Skip folders starting with underscore
        if (str_starts_with($folder->mount_point, "_"))
            continue;

        $wantedGroup = null;
        $wantedGroupIndex = 0;

        // Find database group, and it's array index by id of group on LDAP server
        foreach ($groups as $index => $group) {
            if (!is_null($group->getFolderId()) && $group->getFolderId() == $folder->id) {
                $wantedGroupIndex = $index;
                $wantedGroup = $group;
                break;
            }
        }

        if ($wantedGroup) {
            unset($groups[$wantedGroupIndex]);

            // Update folder on Nextcloud
            updateGroupFolder($client, $wantedGroup, $folder);
            log_message('info', 'NC: Updated folder ' . $wantedGroup->getFolderMountPoint());
        } else {
            // Delete folder on Nextcloud
            deleteGroupFolder($client, $folder->id);
            log_message('info', 'NC: Deleted folder ' . $folder->mount_point);
        }
    }

    foreach ($groups as $group) {
        // Create new folder on Nextcloud
        $id = createGroupFolder($client, $group);

        if ($id != -1) {
            try {
                $group->setFolderId($id);
                saveGroup($group);

                log_message('info', 'NC: Created folder ' . $group->getFolderMountPoint());
            } catch (DatabaseException|ReflectionException $e) {
                log_message('error', 'NC: Unable to create folder ' . $group->getFolderMountPoint() . ': {exception}', ['exception' => $e]);
            }
        }
    }
}

function syncGroupChats(): void
{
    $client = createAPIClient();
    $groups = getGroups();

    foreach ($groups as $group) {
        $members = $group->getMemberships();
        if (empty($members)) {
            log_message('info', 'NC: Skipping chat creation for empty group ' . $group->getDisplayName());
            continue;
        }

        $chatId = $group->getChatId();
        if (!$chatId) {
            $chatId = createGroupChat($client, $group->getDisplayName());

            if ($chatId) {
                try {
                    $group->setChatId($chatId);
                    saveGroup($group);

                    log_message('info', 'NC: Created chat ' . $group->getDisplayName());
                } catch (DatabaseException|ReflectionException $e) {
                    log_message('error', 'NC: Unable to create chat ' . $group->getDisplayName() . ': {exception}', ['exception' => $e]);
                }
            }
        }

        if (!$chatId) {
            log_message('debug', 'NC: No chat id after creation for ' . $group->getDisplayName());
            continue;
        }

        $participants = getGroupChatParticipants($client, $chatId);
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
    log_message('info', "Updated LDAP user '{$user->getUsername()}'");
}

/**
 * Creates a new LDAP entry for a given group
 *
 * @param Group $group
 * @return void
 * @throws LdapRecordException
 */
function createLDAPGroup(Group $group): void
{
    $ldapGroup = new LDAPGroup([
        'uid' => $group->getId(),
        'cn' => $group->getDisplayName(),
        'uniquemember' => getPortalUserDistinguishedName()
    ]);
    $ldapGroup->inside(getGroupsDistinguishedName());
    $ldapGroup->setDn('uid=' . $group->getId() . ',' . getGroupsDistinguishedName());
    $ldapGroup->save();

    log_message('info', "Created LDAP group '{$group->getDisplayName()}'");
}

/**
 * Creates a group folder on the NC server for the given group
 *
 * @throws GuzzleException
 */
function createGroupFolder(Client $client, Group $group): int
{
    $response = $client->post(FOLDERS_API . '/folders', [
        'json' => [
            'mountpoint' => $group->getFolderMountPoint()
        ]
    ]);

    $decodedResponse = json_decode($response->getBody()->getContents());
    $data = $decodedResponse->ocs->data;

    $client->post(FOLDERS_API . '/folders/' . $data->id . '/groups', [
        'json' => [
            'group' => $group->getDisplayName()
        ]
    ]);

    log_message('info', "Created group folder '{$group->getDisplayName()}'");

    return $data->id;
}

/**
 * Updates the LDAP entry for a given group
 *
 * @param LDAPGroup $ldapGroup LDAP entry to be changed
 * @param Group $group updated group
 * @return void
 * @throws LdapRecordException
 */
function updateLDAPGroup(LDAPGroup $ldapGroup, Group $group): void
{
    $ldapGroup->cn = $group->getDisplayName();

    $memberships = $group->getMemberships();

    foreach ($ldapGroup->members()->get() as $ldapMember) {
        if ($ldapMember->getDN() == getPortalUserDistinguishedName()) continue;

        $wantedMember = null;
        $wantedMemberIndex = 0;

        // Find database group, and it's array index by id of group on LDAP server
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
            $ldapGroup->members()->detach($ldapMember);
            log_message('info', 'LDAP: Removed ' . $ldapMember->uid[0] . ' from ' . $group->getDisplayName());
        }
    }

    foreach ($memberships as $membership) {
        $ldapUser = getLDAPUserByUsername($membership->getUser()->getUsername());

        // Add member to ldap server
        $ldapGroup->members()->attach($ldapUser);
        log_message('info', 'LDAP: Added ' . $ldapUser->uid[0] . ' to ' . $group->getDisplayName());
    }

    $ldapGroup->save();
}

/**
 * Updates a given groups' NC group folder
 *
 * @throws GuzzleException
 */
function updateGroupFolder(Client $client, Group $group, object $folder): void
{
    $client->post(FOLDERS_API . '/folders/' . $group->getFolderId() . '/mountpoint', [
        'json' => [
            'mountpoint' => $group->getFolderMountPoint()
        ]
    ]);

    // Add group as member of folder
    $groups = $folder->groups;
    if (empty($groups) || !array_key_exists($group->getDisplayName(), get_object_vars($groups))) {
        $client->post(FOLDERS_API . '/folders/' . $group->getFolderId() . '/groups', [
            'json' => [
                'group' => $group->getDisplayName()
            ]
        ]);
    }

    log_message('info', "Updated group folder for '{$group->getDisplayName()}'");
}

/**
 * Deletes a given groups' NC group folder
 *
 * @throws GuzzleException
 */
function deleteGroupFolder(Client $client, int $id): void
{
    $client->delete(FOLDERS_API . '/folders/' . $id);
    log_message('info', "Deleted group folder '{$id}'");
}

/**
 * @throws GuzzleException
 */
function createGroupChat(Client $client, string $groupName): ?string
{
    $response = $client->post(TALK_API . '/room', [
        'json' => [
            'roomType' => 2,
            'invite' => $groupName,
            'source' => 'portal'
        ]
    ]);

    log_message('info', "Created group chat '{$groupName}'");

    $decodedResponse = json_decode($response->getBody()->getContents());
    return $decodedResponse->ocs->data->token;
}

/**
 * @throws GuzzleException
 */
function getGroupChatParticipants(Client $client, string $chatId): array
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

    log_message('info', "Promoting chat user '{$attendeeId}' in '{$chatId}'");
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

    log_message('info', "Demoting chat user '{$attendeeId}' in '{$chatId}'");
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
function getLDAPGroups(): array|Collection
{
    return LDAPGroup::query()->in(getGroupsDistinguishedName())->paginate();
}

function getUserDistinguishedName(): string
{
    return getenv('ldap.usersDN');
}

function getGroupsDistinguishedName(): string
{
    return getenv('ldap.groupsDN');
}

function getPortalUserDistinguishedName(): string
{
    return getenv('ldap.portalUserDN');
}

/**
 * @throws GuzzleException
 */
function getGroupFolders(Client $client): array
{
    $response = $client->get(FOLDERS_API . '/folders');
    $decodedResponse = json_decode($response->getBody()->getContents());
    $data = $decodedResponse->ocs->data;

    return get_object_vars($data);
}