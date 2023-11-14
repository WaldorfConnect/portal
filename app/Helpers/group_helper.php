<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\GroupMembership;
use App\Entities\MembershipStatus;
use App\Models\GroupMembershipModel;
use App\Models\GroupModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * Returns all groups.
 *
 * @return Group[]
 * @throws DatabaseException
 */
function getGroups(): array
{
    return getGroupModel()->findAll();
}

/**
 * Returns the group corresponding to the given id.
 *
 * @param int $id
 * @return ?Group
 * @throws DatabaseException
 */
function getGroupById(int $id): ?object
{
    return getGroupModel()->find($id);
}

/**
 * Saves the given group model and returns the id of the new entry.
 *
 * @param Group $group
 * @return string|int
 * @throws DatabaseException|ReflectionException
 */
function saveGroup(Group $group): string|int
{
    $model = new GroupModel();
    $model->save($group);
    return $model->getInsertID();
}

/**
 * Creates a group model with the given parameters.
 *
 * @param string $name
 * @param int $regionId
 * @return Group
 */
function createGroup(string $name, int $regionId): Group
{
    $group = new Group();
    $group->setName($name);
    $group->setRegionId($regionId);
    return $group;
}

/**
 * Deletes the group with the corresponding id.
 *
 * @param int $id
 * @return void
 */
function deleteGroup(int $id): void
{
    getGroupModel()->delete($id);
}

/**
 * Returns the group membership (or join request) for a given user in a given group.
 *
 * @param int $userId
 * @param int $groupId
 * @return ?GroupMembership
 */
function getGroupMembership(int $userId, int $groupId): ?object
{
    return getGroupMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->first();
}

/**
 * Returns the group memberships (or join requests) for a given user.
 *
 * @param int $userId
 * @return GroupMembership[]
 */
function getGroupMembershipsByUserId(int $userId): array
{
    return getGroupMembershipModel()->where('user_id', $userId)->findAll();
}

/**
 * Returns the group memberships (or join requests) for a given group.
 *
 * @param int $groupId
 * @return GroupMembership[]
 */
function getGroupMembershipsByGroupId(int $groupId): array
{
    return getGroupMembershipModel()->where('group_id', $groupId)->findAll();
}

/**
 * Returns the members (join requests excluded) for a given group.
 *
 * @param int $groupId
 * @return GroupMembership[]
 */
function getGroupMembers(int $groupId): array
{
    return getGroupMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->findAll();
}

/**
 * Returns count of members (join requests excluded) for a given group.
 *
 * @param int $groupId
 * @return int
 */
function countGroupMembers(int $groupId): int
{
    return getGroupMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->countAllResults();
}

/**
 * Returns the join requests (members excluded) for a given group.
 *
 * @param int $groupId
 * @return GroupMembership[]
 */
function getGroupJoinRequests(int $groupId): array
{
    return getGroupMembershipModel()->where('group_id', $groupId)->where('status', MembershipStatus::PENDING->value)->findAll();
}

/**
 * Returns all groups in a given region.
 *
 * @param int $regionId
 * @return Group[]
 * @throws DatabaseException
 */
function getGroupsByRegionId(int $regionId): array
{
    return getGroupModel()->where('region_id', $regionId)->findAll();
}

/**
 * Creates a group membership request with the given parameters.
 *
 * @param int $userId
 * @param int $groupId
 * @return void
 * @throws DatabaseException
 * @throws ReflectionException
 */
function createGroupMembershipRequest(int $userId, int $groupId): void
{
    $membership = new GroupMembership();
    $membership->setUserId($userId);
    $membership->setGroupId($groupId);
    $membership->setStatus(MembershipStatus::PENDING);
    saveMembership($membership);
}

function deleteGroupMembership(int $userId, int $groupId): void
{
    getGroupMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->delete();
}

/**
 * Saves given membership model.
 *
 * @param GroupMembership $membership
 * @return void
 * @throws ReflectionException
 */
function saveMembership(GroupMembership $membership): void
{
    getGroupMembershipModel()->save($membership);
}

/**
 * Returns the group table wrapper and query builder.
 *
 * @return GroupModel
 */
function getGroupModel(): GroupModel
{
    return new GroupModel();
}

/**
 * Returns the group membership table wrapper and query builder.
 *
 * @return GroupMembershipModel
 */
function getGroupMembershipModel(): GroupMembershipModel
{
    return new GroupMembershipModel();
}
