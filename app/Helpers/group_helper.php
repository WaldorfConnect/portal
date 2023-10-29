<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\GroupMembership;
use App\Entities\MembershipStatus;
use App\Entities\User;
use App\Models\GroupMembershipModel;
use App\Models\GroupModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
use ReflectionException;

/**
 * @return Group[]
 * @throws DatabaseException
 */
function getGroups(): array
{
    return getGroupModel()->findAll();
}

/**
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

function createGroup(string $name, int $regionId): Group
{
    $group = new Group();
    $group->setName($name);
    $group->setRegionId($regionId);
    return $group;
}

function deleteGroup(int $id): void
{
    getGroupModel()->delete($id);
}

/**
 * @param int $userId
 * @return GroupMembership[]
 */
function getGroupMembershipsByUserId(int $userId): array
{
    return getGroupMembershipModel()->where('user_id', $userId)->findAll();
}

/**
 * @param int $groupId
 * @return GroupMembership[]
 */
function getGroupMembershipsByGroupId(int $groupId): array
{
    return getGroupMembershipModel()->where('group_id', $groupId)->findAll();
}

/**
 * @param int $groupId
 * @return ?GroupMembership
 */
function getGroupMembershipsByUserIdAndGroupId(int $userId, int $groupId): ?object
{
    return getGroupMembershipModel()->where('user_id', $userId)->where('group_id', $groupId)->first();
}

/**
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
 * @param int $userId
 * @param int $groupId
 * @return void
 * @throws ReflectionException
 * @throws DatabaseException
 */
function createGroupMembershipRequest(int $userId, int $groupId): void
{
    $membership = new GroupMembership();
    $membership->setUserId($userId);
    $membership->setGroupId($groupId);
    $membership->setStatus(MembershipStatus::PENDING);
    getGroupMembershipModel()->save($membership);
}

/**
 * @param int $userId
 * @param int $groupId
 * @param MembershipStatus $status
 * @return void
 */
function setGroupMembershipStatus(int $userId, int $groupId, MembershipStatus $status): void
{

}

/**
 * @return GroupModel
 */
function getGroupModel(): GroupModel
{
    return new GroupModel();
}

/**
 * @return GroupMembershipModel
 */
function getGroupMembershipModel(): GroupMembershipModel
{
    return new GroupMembershipModel();
}
