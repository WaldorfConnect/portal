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
