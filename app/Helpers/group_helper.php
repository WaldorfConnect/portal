<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\Membership;
use App\Entities\MembershipStatus;
use App\Models\MembershipModel;
use App\Models\GroupModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * Return all groups
 *
 * @return Group[]
 * @throws DatabaseException
 */
function getGroups(): array
{
    return getGroupModel()->findAll();
}

/**
 * Returns all groups where name matches
 *
 * @param string $name group name
 * @return Group[]
 */
function getGroupsByName(string $name): array
{
    return getGroupModel()->like('name', $name)->orLike('short_name', $name)->findAll();
}

/**
 * Returns the group corresponding to the given id
 *
 * @param int $id group id
 * @return ?Group
 * @throws DatabaseException
 */
function getGroupById(int $id): ?object
{
    return getGroupModel()->find($id);
}

/**
 * Get a group's children by its parent id
 *
 * @param int $parentId parent group id
 * @return array
 */
function getChildGroupsByParentId(int $parentId): array
{
    return getGroupModel()->where(['parent_id' => $parentId])->findAll();
}

/**
 * Updates a given group in the database
 *
 * @param Group $group the modified group
 * @return void
 * @throws ReflectionException
 */
function saveGroup(Group $group): void
{
    if (!$group->hasChanged()) {
        return;
    }

    getGroupModel()->save($group);
    log_message('info', "Saving group '{$group->getDisplayName()}'");
}

/**
 * Creates a group with the given parameters
 *
 * @param string $name
 * @param string $shortName
 * @param int $regionId
 * @param int|null $parentId
 * @return Group
 */
function createGroup(string $name, string $shortName, int $regionId, int $parentId = null): Group
{
    $group = new Group();
    $group->setName($name);
    $group->setShortName($shortName);
    $group->setRegionId($regionId);
    $group->setParentId($parentId);
    return $group;
}

/**
 * Inserts a group and returns its newly created id
 *
 * @throws ReflectionException
 */
function insertGroup(Group $group): string|int
{
    $model = getGroupModel();
    $model->insert($group);

    log_message('info', "Inserting group '{$group->getDisplayName()}'");
    return $model->getInsertID();
}

/**
 * Deletes the group with the corresponding id
 *
 * @param int $id
 * @return void
 */
function deleteGroup(int $id): void
{
    getGroupModel()->delete($id);
    log_message('info', "Deleted group '{$id}'");
}

/**
 * Returns the membership (or join request) for a given user in a given group
 *
 * @param int $userId
 * @param int $groupId
 * @return ?Membership
 */
function getMembership(int $userId, int $groupId): ?object
{
    return getMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->first();
}

/**
 * Returns the memberships (or join requests) for a given user
 *
 * @param int $userId
 * @return Membership[]
 */
function getMembershipsByUserId(int $userId): array
{
    return getMembershipModel()->where('user_id', $userId)->findAll();
}

/**
 * Returns the memberships (or join requests) for a given group
 *
 * @param int $groupId
 * @return Membership[]
 */
function getMembershipsByGroupId(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->findAll();
}

/**
 * Returns the members (join requests excluded) for a given group
 *
 * @param int $groupId
 * @return Membership[]
 */
function getMembers(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->findAll();
}

/**
 * Returns count of members (join requests excluded) for a given group
 *
 * @param int $groupId
 * @return int
 */
function countMembers(int $groupId): int
{
    return getMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->countAllResults();
}

/**
 * Returns all groups in a given region
 *
 * @param int $regionId
 * @return Group[]
 * @throws DatabaseException
 */
function getGroupsByRegionId(int $regionId): array
{
    return getGroupModel()->where('region_id', $regionId)->where('parent_id', null)->findAll();
}

/**
 * Creates a membership request with the given parameters
 *
 * @param int $userId
 * @param int $groupId
 * @return void
 * @throws DatabaseException
 * @throws ReflectionException
 */
function createMembershipRequest(int $userId, int $groupId): void
{
    $membership = new Membership();
    $membership->setUserId($userId);
    $membership->setGroupId($groupId);
    $membership->setStatus(MembershipStatus::PENDING);
    saveMembership($membership);

    log_message('info', "Created membership request for '{$userId} and group '{$groupId}'");
}

/**
 * Creates membership with given parameters
 *
 * @param int $userId
 * @param int $groupId
 * @param MembershipStatus $status
 * @return void
 * @throws ReflectionException
 */
function createMembership(int $userId, int $groupId, MembershipStatus $status = MembershipStatus::USER): void
{
    $membership = new Membership();
    $membership->setUserId($userId);
    $membership->setGroupId($groupId);
    $membership->setStatus($status);
    saveMembership($membership);

    log_message('info', "Created membership for '{$userId} in group '{$groupId}' with status '{$status->value}'");
}

/**
 * Deletes a group membership
 *
 * @param int $userId
 * @param int $groupId
 * @return void
 */
function deleteMembership(int $userId, int $groupId): void
{
    getMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->delete();
    log_message('info', "Deleted membership for '{$userId} in group '{$groupId}'");
}

/**
 * Saves given membership model
 *
 * @param Membership $membership
 * @return void
 * @throws ReflectionException
 */
function saveMembership(Membership $membership): void
{
    getMembershipModel()->save($membership);
    log_message('info', "Saved membership for '{$membership->getUserId()} in group '{$membership->getGroupId()}'");
}

/**
 * Create a notification for all group members
 *
 * @param int $groupId
 * @param string $subject
 * @param string $body
 * @param MembershipStatus|null $status
 * @param array $exceptUsers
 * @return void
 */
function createGroupNotification(int $groupId, string $subject, string $body, MembershipStatus $status = null, array $exceptUsers = []): void
{
    $group = getGroupById($groupId);
    $body = sprintf($body, $group->getUrl());

    foreach ($group->getMemberships() as $membership) {
        // If user isn't an accepted member of the group
        if ($membership->getStatus() == MembershipStatus::PENDING) {
            continue;
        }

        // If user hasn't got given membership status
        if ($status != null && $membership->getStatus() != $status) {
            continue;
        }

        // If user is in given exception list
        if (in_array($membership->getUserId(), $exceptUsers)) {
            continue;
        }

        createNotification($membership->getUserId(), $subject, $body);
    }

    log_message('info', "Created group notification in group '{$groupId}' with subject '{$subject}' and body '{$body}'");
}

/**
 * Returns the group table wrapper and query builder
 *
 * @return GroupModel
 */
function getGroupModel(): GroupModel
{
    return new GroupModel();
}

/**
 * Returns the group membership table wrapper and query builder
 *
 * @return MembershipModel
 */
function getMembershipModel(): MembershipModel
{
    return new MembershipModel();
}
