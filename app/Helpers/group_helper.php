<?php

namespace App\Helpers;

use App\Entities\Group;
use App\Entities\MembershipStatus;
use App\Exceptions\Group\GroupNotFoundException;
use App\Exceptions\User\UserNotFoundException;
use App\Models\GroupModel;

function getGroups(): array
{
    return getGroupModel()->findAll();
}

function getGroupsByName(string $name): array
{
    return getGroupModel()->like('name', $name)->orLike('short_name', $name)->findAll();
}

function getGroupById(int $id): ?object
{
    return getGroupModel()->find($id);
}

function getChildGroupsByParentId(int $parentId): array
{
    return getGroupModel()->where(['parent_id' => $parentId])->findAll();
}

function saveGroup(Group $group): void
{
    if (!$group->hasChanged()) {
        return;
    }

    getGroupModel()->save($group);
    log_message('info', "Saving group '{$group->getDisplayName()}'");
}

function createGroup(string $name, string $shortName, int $regionId, int $parentId = null): Group
{
    $group = new Group();
    $group->setName($name);
    $group->setShortName($shortName);
    $group->setRegionId($regionId);
    $group->setParentId($parentId);
    return $group;
}

function insertGroup(Group $group): string|int
{
    $model = getGroupModel();
    $model->insert($group);

    log_message('info', "Inserting group '{$group->getDisplayName()}'");
    return $model->getInsertID();
}

function deleteGroup(int $id): void
{
    getGroupModel()->delete($id);
    log_message('info', "Deleted group '{$id}'");
}

function getParentGroupsByRegionId(int $regionId): array
{
    return getGroupModel()->where('region_id', $regionId)->where('parent_id', null)->findAll();
}

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
 * @throws GroupNotFoundException
 */
function validateGroup(int $groupId): void
{
    $group = getGroupById($groupId);
    if (!$group) {
        throw new GroupNotFoundException();
    }
}

function getGroupModel(): GroupModel
{
    return new GroupModel();
}