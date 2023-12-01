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
 * @param string $websiteUrl
 * @param int $regionId
 * @return Group
 */
function createGroup(string $name, string $websiteUrl, int $regionId): Group
{
    $group = new Group();
    $group->setName($name);
    $group->setWebsiteUrl($websiteUrl);
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
 * Returns the membership (or join request) for a given user in a given group.
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
 * Returns the memberships (or join requests) for a given user.
 *
 * @param int $userId
 * @return Membership[]
 */
function getMembershipsByUserId(int $userId): array
{
    return getMembershipModel()->where('user_id', $userId)->findAll();
}

/**
 * Returns the memberships (or join requests) for a given group.
 *
 * @param int $groupId
 * @return Membership[]
 */
function getMembershipsByGroupId(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->findAll();
}

/**
 * Returns the members (join requests excluded) for a given group.
 *
 * @param int $groupId
 * @return Membership[]
 */
function getMembers(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->findAll();
}

/**
 * Returns count of members (join requests excluded) for a given group.
 *
 * @param int $groupId
 * @return int
 */
function countMembers(int $groupId): int
{
    return getMembershipModel()->where('group_id', $groupId)->whereNotIn('status', [MembershipStatus::PENDING->value])->countAllResults();
}

/**
 * Returns the join requests (members excluded) for a given group.
 *
 * @param int $groupId
 * @return Membership[]
 */
function getJoinRequests(int $groupId): array
{
    return getMembershipModel()->where('group_id', $groupId)->where('status', MembershipStatus::PENDING->value)->findAll();
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
function createMembershipRequest(int $userId, int $groupId): void
{
    $membership = new Membership();
    $membership->setUserId($userId);
    $membership->setGroupId($groupId);
    $membership->setStatus(MembershipStatus::PENDING);
    saveMembership($membership);
}

function deleteMembership(int $userId, int $groupId): void
{
    getMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->delete();
}

/**
 * Saves given membership model.
 *
 * @param Membership $membership
 * @return void
 * @throws ReflectionException
 */
function saveMembership(Membership $membership): void
{
    getMembershipModel()->save($membership);
}

/**
 * Returns whether a given user has administrative permissions for the given group.
 *
 * @param int $userId
 * @param int $groupId
 * @return bool
 */
function isGroupAdmin(int $userId, int $groupId): bool
{
    $membership = getMembership($userId, $groupId);
    return $membership && $membership->getStatus() == MembershipStatus::ADMIN;
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
 * @return MembershipModel
 */
function getMembershipModel(): MembershipModel
{
    return new MembershipModel();
}
